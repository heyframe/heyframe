<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayException;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\AddShippingMethodExtensionCommand;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodEntity;
use HeyFrame\Core\Framework\Log\ExceptionLogger;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AddShippingMethodExtensionsCommandHandler extends AbstractCheckoutGatewayCommandHandler
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
            AddShippingMethodExtensionCommand::class,
        ];
    }

    /**
     * @param AddShippingMethodExtensionCommand $command
     */
    public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void
    {
        $method = $response->getAvailableShippingMethods()->filter(function (ShippingMethodEntity $method) use ($command) {
            return $method->getTechnicalName() === $command->shippingMethodTechnicalName;
        })->first();

        if (!$method) {
            $this->logger->logOrThrowException(
                CheckoutGatewayException::handlerException('Shipping method "{{ technicalName }}" not found', ['technicalName' => $command->shippingMethodTechnicalName])
            );

            return;
        }

        $method->addExtensions([$command->extensionKey => new ArrayStruct($command->extensionsPayload)]);
    }
}
