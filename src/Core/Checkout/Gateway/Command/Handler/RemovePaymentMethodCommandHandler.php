<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\RemovePaymentMethodCommand;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class RemovePaymentMethodCommandHandler extends AbstractCheckoutGatewayCommandHandler
{
    public static function supportedCommands(): array
    {
        return [
            RemovePaymentMethodCommand::class,
        ];
    }

    /**
     * @param RemovePaymentMethodCommand $command
     */
    public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void
    {
        $technicalName = $command->paymentMethodTechnicalName;
        $methods = $response->getAvailablePaymentMethods();

        $methods = $methods->filter(function (PaymentMethodEntity $method) use ($technicalName) {
            return $method->getTechnicalName() !== $technicalName;
        });

        $response->setAvailablePaymentMethods($methods);
    }
}
