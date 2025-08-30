<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Registration;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Exception\AppRegistrationException;
use HeyFrame\Core\Framework\App\Exception\InstanceIdChangeSuggestedException;
use HeyFrame\Core\Framework\App\Hmac\Guzzle\AuthMiddleware;
use HeyFrame\Core\Framework\App\InstanceId\InstanceIdProvider;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppRegistrationService
{
    /**
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly HandshakeFactory $handshakeFactory,
        private readonly Client $httpClient,
        private readonly EntityRepository $appRepository,
        private readonly string $shopUrl,
        private readonly InstanceIdProvider $instanceIdProvider,
        private readonly string $heyframeVersion
    ) {
    }

    public function registerApp(Manifest $manifest, string $id, #[\SensitiveParameter] string $secretAccessKey, Context $context): void
    {
        if (!$manifest->getSetup()) {
            return;
        }

        try {
            $appName = $manifest->getMetadata()->getName();
            $appResponse = $this->registerWithApp($manifest, $context);

            $secret = $appResponse['secret'];
            $confirmationUrl = $appResponse['confirmation_url'];

            $this->saveAppSecret($id, $context, $secret);

            $this->confirmRegistration($id, $context, $secret, $secretAccessKey, $confirmationUrl);
        } catch (RequestException $e) {
            if ($e->hasResponse() && $e->getResponse() !== null) {
                $response = $e->getResponse();
                $data = json_decode($response->getBody()->getContents(), true);

                if (isset($data['error']) && \is_string($data['error'])) {
                    throw AppException::registrationFailed($appName, $data['error']);
                }
            }

            throw AppException::registrationFailed($appName, $e->getMessage(), $e);
        } catch (GuzzleException $e) {
            throw AppException::registrationFailed($appName, $e->getMessage(), $e);
        }
    }

    /**
     * @throws GuzzleException
     *
     * @return array<string, string>
     */
    private function registerWithApp(Manifest $manifest, Context $context): array
    {
        $handshake = $this->handshakeFactory->create($manifest);

        $request = $handshake->assembleRequest();
        $response = $this->httpClient->send($request, [AuthMiddleware::APP_REQUEST_CONTEXT => $context]);

        return $this->parseResponse($manifest->getMetadata()->getName(), $handshake, $response);
    }

    private function saveAppSecret(string $id, Context $context, #[\SensitiveParameter] string $secret): void
    {
        $update = ['id' => $id, 'appSecret' => $secret];

        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($update): void {
            $this->appRepository->update([$update], $context);
        });
    }

    private function confirmRegistration(
        string $id,
        Context $context,
        #[\SensitiveParameter]
        string $secret,
        #[\SensitiveParameter]
        string $secretAccessKey,
        string $confirmationUrl
    ): void {
        $payload = $this->getConfirmationPayload($id, $secretAccessKey, $context);

        $signature = $this->signPayload($payload, $secret);

        $this->httpClient->post($confirmationUrl, [
            'headers' => [
                'heyframe-shop-signature' => $signature,
                'sw-version' => $this->heyframeVersion,
            ],
            AuthMiddleware::APP_REQUEST_CONTEXT => $context,
            'json' => $payload,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function parseResponse(
        string $appName,
        AppHandshakeInterface $handshake,
        ResponseInterface $response
    ): array {
        try {
            $data = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw AppException::registrationFailed($appName, 'JSON response could not be decoded', $e);
        }

        if (isset($data['error']) && \is_string($data['error'])) {
            throw AppException::registrationFailed($appName, $data['error']);
        }

        $proof = $data['proof'] ?? '';

        if (!\is_string($proof)) {
            throw AppException::registrationFailed($appName, 'The app server provided no proof');
        }

        if (!hash_equals($handshake->fetchAppProof(), trim($proof))) {
            throw AppException::registrationFailed($appName, 'The app server provided an invalid proof');
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function getConfirmationPayload(string $id, #[\SensitiveParameter] string $secretAccessKey, Context $context): array
    {
        $app = $this->getApp($id, $context);

        try {
            $instanceId = $this->instanceIdProvider->getInstanceId();
        } catch (InstanceIdChangeSuggestedException $e) {
            throw AppRegistrationException::registrationFailed(
                $app->getName(),
                $e->getMessage(),
            );
        }

        // We can safely assume that the app has an integration because it is created together with the app
        // and explicitly fetched in the ::getApp() method below.
        $integration = $app->getIntegration();
        \assert($integration !== null);

        return [
            'apiKey' => $integration->getAccessKey(),
            'secretKey' => $secretAccessKey,
            'timestamp' => (string) (new \DateTime())->getTimestamp(),
            'shopUrl' => $this->shopUrl,
            'instanceId' => $instanceId,
        ];
    }

    /**
     * @param array<string, string> $body
     */
    private function signPayload(array $body, #[\SensitiveParameter] string $secret): string
    {
        return hash_hmac('sha256', json_encode($body, \JSON_THROW_ON_ERROR), $secret);
    }

    private function getApp(string $id, Context $context): AppEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('integration');

        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();
        \assert($app !== null);

        return $app;
    }
}
