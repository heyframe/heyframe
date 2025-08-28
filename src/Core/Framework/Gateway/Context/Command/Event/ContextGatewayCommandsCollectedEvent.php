<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Event;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Hook\CartAware;
use HeyFrame\Core\Framework\App\Context\Gateway\AppContextGateway;
use HeyFrame\Core\Framework\Gateway\Context\Command\ContextGatewayCommandCollection;
use HeyFrame\Core\Framework\Gateway\Context\Command\Struct\ContextGatewayPayloadStruct;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is dispatched after the app context gateway has collected all context commands.
 * It can be used to add custom commands, which should be executed with all other commands.
 *
 * @see AppContextGateway::process() for an example implementation
 */
#[Package('framework')]
class ContextGatewayCommandsCollectedEvent extends Event implements CartAware
{
    public function __construct(
        private readonly ContextGatewayPayloadStruct $payload,
        private readonly ContextGatewayCommandCollection $commands,
    ) {
    }

    public function getPayload(): ContextGatewayPayloadStruct
    {
        return $this->payload;
    }

    public function getCommands(): ContextGatewayCommandCollection
    {
        return $this->commands;
    }

    public function getCart(): Cart
    {
        return $this->payload->getCart();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->payload->getChannelContext();
    }
}
