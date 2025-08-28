<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\AddCartErrorCommand;
use HeyFrame\Core\Checkout\Gateway\Error\CheckoutGatewayError;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AddCartErrorCommandHandler extends AbstractCheckoutGatewayCommandHandler
{
    public static function supportedCommands(): array
    {
        return [
            AddCartErrorCommand::class,
        ];
    }

    /**
     * @param AddCartErrorCommand $command
     */
    public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void
    {
        $response->getCartErrors()->add(new CheckoutGatewayError($command->message, $command->level, $command->blocking));
    }
}
