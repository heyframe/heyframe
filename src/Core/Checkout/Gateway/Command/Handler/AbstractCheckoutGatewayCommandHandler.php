<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
abstract class AbstractCheckoutGatewayCommandHandler
{
    abstract public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void;

    /**
     * @return array<class-string<AbstractCheckoutGatewayCommand>>
     */
    abstract public static function supportedCommands(): array;
}
