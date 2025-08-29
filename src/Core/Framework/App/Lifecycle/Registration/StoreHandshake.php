<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Registration;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Services\StoreClient;
use Psr\Http\Message\RequestInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class StoreHandshake implements AppHandshakeInterface
{
    private const SBP_EXCEPTION_UNAUTHORIZED = 'HeyFramePlatformException-1';

    private const SBP_EXCEPTION_NO_LICENSE = 'HeyFramePlatformException-16';

    public function __construct(
        private readonly string $shopUrl,
        private readonly string $appEndpoint,
        private readonly string $appName,
        private readonly string $instanceId,
        private readonly StoreClient $storeClient,
        private readonly string $heyframeVersion
    ) {
    }

    public function assembleRequest(): RequestInterface
    {
        $date = new \DateTime();
        $uri = new Uri($this->appEndpoint);

        $uri = Uri::withQueryValues($uri, [
            'shop-id' => $this->instanceId,
            'shop-url' => $this->shopUrl,
            'timestamp' => (string) $date->getTimestamp(),
        ]);

        $signature = $this->signPayload($uri->getQuery());

        return new Request(
            'GET',
            $uri,
            [
                'heyframe-app-signature' => $signature,
                'sw-version' => $this->heyframeVersion,
            ]
        );
    }

    public function fetchAppProof(): string
    {
        $proof = $this->instanceId . $this->shopUrl . $this->appName;

        return $this->storeClient->signPayloadWithAppSecret($proof, $this->appName);
    }

    private function signPayload(string $payload): string
    {
        try {
            return $this->storeClient->signPayloadWithAppSecret($payload, $this->appName);
        } catch (\Exception $e) {
            if ($e instanceof ClientException) {
                $response = \json_decode($e->getResponse()->getBody()->getContents(), true, \JSON_THROW_ON_ERROR, \JSON_THROW_ON_ERROR);

                if ($response['code'] === self::SBP_EXCEPTION_UNAUTHORIZED || $response['code'] === self::SBP_EXCEPTION_NO_LICENSE) {
                    throw AppException::licenseCouldNotBeVerified($this->appName, $e);
                }
            }

            throw AppException::registrationFailed(
                $this->appName,
                'Could not sign payload with store secret',
                $e
            );
        }
    }
}
