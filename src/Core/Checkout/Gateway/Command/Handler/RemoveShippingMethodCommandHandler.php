<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\RemoveShippingMethodCommand;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class RemoveShippingMethodCommandHandler extends AbstractCheckoutGatewayCommandHandler
{
    public static function supportedCommands(): array
    {
        return [
            RemoveShippingMethodCommand::class,
        ];
    }

    /**
     * @param RemoveShippingMethodCommand $command
     */
    public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void
    {
        $technicalName = $command->shippingMethodTechnicalName;
        $methods = $response->getAvailableShippingMethods();

        $methods = $methods->filter(function (ShippingMethodEntity $method) use ($technicalName) {
            return $method->getTechnicalName() !== $technicalName;
        });

        $response->setAvailableShippingMethods($methods);
    }
}
