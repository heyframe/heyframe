<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Channel\CartService;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Context\ChannelContextPersister;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;

#[Package('checkout')]
class ApiOrderCartService
{
    /**
     * @internal
     */
    public function __construct(
        protected CartService $cartService,
        protected ChannelContextPersister $contextPersister
    ) {
    }

    public function addPermission(string $token, string $permission, string $channelId): void
    {
        $payload = $this->contextPersister->load($token, $channelId);

        if (!\array_key_exists(ChannelContextService::PERMISSIONS, $payload)) {
            $payload[ChannelContextService::PERMISSIONS] = [];
        }

        $payload[ChannelContextService::PERMISSIONS][$permission] = true;
        $this->contextPersister->save($token, $payload, $channelId);
    }

    public function deletePermission(string $token, string $permission, string $channelId): void
    {
        $payload = $this->contextPersister->load($token, $channelId);
        $payload[ChannelContextService::PERMISSIONS][$permission] = false;

        $this->contextPersister->save($token, $payload, $channelId);
    }
}
