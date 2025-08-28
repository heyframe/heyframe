<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Registration;

use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Exception\ShopIdChangeSuggestedException;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\ShopId\ShopIdProvider;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Services\StoreClient;

/**
 * @internal only for use by the app-system
 *
 * @final
 */
#[Package('framework')]
class HandshakeFactory
{
    public function __construct(
        private readonly string $shopUrl,
        private readonly ShopIdProvider $shopIdProvider,
        private readonly StoreClient $storeClient,
        private readonly string $heyframeVersion
    ) {
    }

    public function create(Manifest $manifest): AppHandshakeInterface
    {
        $setup = $manifest->getSetup();
        $metadata = $manifest->getMetadata();
        $appName = $metadata->getName();

        if (!$setup) {
            throw AppException::registrationFailed(
                $appName,
                \sprintf('No setup for registration provided in manifest for app "%s".', $metadata->getName())
            );
        }

        $privateSecret = $setup->getSecret();

        try {
            $shopId = $this->shopIdProvider->getShopId();
        } catch (ShopIdChangeSuggestedException $e) {
            throw AppException::registrationFailed(
                $appName,
                $e->getMessage(),
            );
        }

        if ($privateSecret) {
            return new PrivateHandshake(
                $this->shopUrl,
                $privateSecret,
                $setup->getRegistrationUrl(),
                $metadata->getName(),
                $shopId,
                $this->heyframeVersion
            );
        }

        return new StoreHandshake(
            $this->shopUrl,
            $setup->getRegistrationUrl(),
            $metadata->getName(),
            $shopId,
            $this->storeClient,
            $this->heyframeVersion
        );
    }
}
