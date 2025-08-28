<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayException;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\AddPaymentMethodExtensionCommand;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Framework\Log\ExceptionLogger;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AddPaymentMethodExtensionsCommandHandler extends AbstractCheckoutGatewayCommandHandler
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ExceptionLogger $logger,
    ) {
    }

    public static function supportedCommands(): array
    {
        return [
            AddPaymentMethodExtensionCommand::class,
        ];
    }

    /**
     * @param AddPaymentMethodExtensionCommand $command
     */
    public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void
    {
        $method = $response->getAvailablePaymentMethods()->filter(function (PaymentMethodEntity $method) use ($command) {
            return $method->getTechnicalName() === $command->paymentMethodTechnicalName;
        })->first();

        if (!$method) {
            $this->logger->logOrThrowException(
                CheckoutGatewayException::handlerException('Payment method "{{ technicalName }}" not found', ['technicalName' => $command->paymentMethodTechnicalName])
            );

            return;
        }

        $method->addExtensions([$command->extensionKey => new ArrayStruct($command->extensionsPayload)]);
    }
}
