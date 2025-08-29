<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Payload;

use HeyFrame\Core\Framework\Api\Serializer\JsonEntityEncoder;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Exception\InstanceIdChangeSuggestedException;
use HeyFrame\Core\Framework\App\Hmac\Guzzle\AuthMiddleware;
use HeyFrame\Core\Framework\App\InstanceId\InstanceIdProvider;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\InAppPurchase;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppPayloadServiceHelper
{
    /**
     * @internal
     */
    public function __construct(
        private readonly DefinitionInstanceRegistry $definitionRegistry,
        private readonly JsonEntityEncoder $entityEncoder,
        private readonly InstanceIdProvider $instanceIdProvider,
        private readonly InAppPurchase $inAppPurchase,
        private readonly string $shopUrl,
    ) {
    }

    /**
     * @throws InstanceIdChangeSuggestedException
     */
    public function buildSource(string $appVersion, string $appName): Source
    {
        return new Source(
            $this->shopUrl,
            $this->instanceIdProvider->getInstanceId(),
            $appVersion,
            $this->inAppPurchase->getJWTByExtension($appName),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function encode(SourcedPayloadInterface $payload): array
    {
        $array = $payload->jsonSerialize();

        foreach ($array as $propertyName => $property) {
            if ($property instanceof ChannelContext) {
                $channelContext = $property->jsonSerialize();

                foreach ($channelContext as $subPropertyName => $subProperty) {
                    if (!$subProperty instanceof Entity) {
                        continue;
                    }

                    $channelContext[$subPropertyName] = $this->encodeEntity($subProperty);
                }

                $array[$propertyName] = $channelContext;
            }

            if (!$property instanceof Entity) {
                continue;
            }

            $array[$propertyName] = $this->encodeEntity($property);
        }

        return $array;
    }

    /**
     * @param array{timeout?: int} $additionalOptions
     */
    public function createRequestOptions(
        SourcedPayloadInterface $payload,
        AppEntity $app,
        Context $context,
        array $additionalOptions = []
    ): AppPayloadStruct {
        if (!$app->getAppSecret()) {
            throw AppException::registrationFailed($app->getName(), 'App secret is missing');
        }

        $defaultOptions = [
            AuthMiddleware::APP_REQUEST_CONTEXT => $context,
            AuthMiddleware::APP_REQUEST_TYPE => [
                AuthMiddleware::APP_SECRET => $app->getAppSecret(),
                AuthMiddleware::VALIDATED_RESPONSE => true,
            ],
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $this->buildPayload($payload, $app),
        ];

        return new AppPayloadStruct(\array_merge($defaultOptions, $additionalOptions));
    }

    private function buildPayload(SourcedPayloadInterface $payload, AppEntity $app): string
    {
        $payload->setSource($this->buildSource($app->getVersion(), $app->getName()));
        $encoded = $this->encode($payload);

        return \json_encode($encoded, \JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<string, mixed>
     */
    private function encodeEntity(Entity $entity): array
    {
        $definition = $this->definitionRegistry->getByEntityName($entity->getApiAlias());

        return $this->entityEncoder->encode(
            new Criteria(),
            $definition,
            $entity,
            '/api'
        );
    }
}
