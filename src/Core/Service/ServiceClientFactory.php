<?php declare(strict_types=1);

namespace HeyFrame\Core\Service;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Exception\ShopIdChangeSuggestedException;
use HeyFrame\Core\Framework\App\Hmac\Guzzle\AuthMiddleware;
use HeyFrame\Core\Framework\App\Payload\AppPayloadServiceHelper;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\ServiceRegistry\Client as ServiceRegistryClient;
use HeyFrame\Core\Service\ServiceRegistry\ServiceEntry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 */
#[Package('framework')]
class ServiceClientFactory
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ServiceRegistryClient $serviceRegistryClient,
        private readonly string $heyframeVersion,
        private readonly AuthMiddleware $authMiddleware,
        private readonly AppPayloadServiceHelper $appPayloadServiceHelper,
    ) {
    }

    public function newFor(ServiceEntry $entry): ServiceClient
    {
        return new ServiceClient(
            $this->client->withOptions([
                'base_uri' => $entry->host,
            ]),
            $this->heyframeVersion,
            $entry,
            new Filesystem()
        );
    }

    /**
     * @throws ShopIdChangeSuggestedException
     */
    public function newAuthenticatedFor(ServiceEntry $entry, AppEntity $app, Context $context): AuthenticatedServiceClient
    {
        if (!$app->getAppSecret()) {
            throw ServiceException::missingAppSecretInfo($app->getId());
        }

        $stack = HandlerStack::create();
        $stack->push($this->authMiddleware);

        $authClient = new Client([
            'base_uri' => $entry->host,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            AuthMiddleware::APP_REQUEST_CONTEXT => $context,
            AuthMiddleware::APP_REQUEST_TYPE => [
                AuthMiddleware::APP_SECRET => $app->getAppSecret(),
            ],
            'handler' => $stack,
        ]);

        return new AuthenticatedServiceClient(
            $authClient,
            $entry,
            $this->appPayloadServiceHelper->buildSource($app->getVersion(), $app->getName())
        );
    }

    public function fromName(string $name): ServiceClient
    {
        return $this->newFor(
            $this->serviceRegistryClient->get($name)
        );
    }
}
