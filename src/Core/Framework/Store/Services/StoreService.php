<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Struct\AccessTokenStruct;
use HeyFrame\Core\System\User\UserCollection;

/**
 * @internal
 */
#[Package('checkout')]
class StoreService
{
    final public const CONFIG_KEY_STORE_LICENSE_DOMAIN = 'core.store.licenseHost';
    final public const CONFIG_KEY_STORE_LICENSE_EDITION = 'core.store.licenseEdition';

    /**
     * @param EntityRepository<UserCollection> $userRepository
     */
    final public function __construct(private readonly EntityRepository $userRepository)
    {
    }

    public function updateStoreToken(Context $context, AccessTokenStruct $accessToken): void
    {
        /** @var AdminApiSource $contextSource */
        $contextSource = $context->getSource();
        $userId = $contextSource->getUserId();

        $storeToken = $accessToken->getShopUserToken()->getToken();

        $context->scope(Context::SYSTEM_SCOPE, function ($context) use ($userId, $storeToken): void {
            $this->userRepository->update([['id' => $userId, 'storeToken' => $storeToken]], $context);
        });
    }
}
