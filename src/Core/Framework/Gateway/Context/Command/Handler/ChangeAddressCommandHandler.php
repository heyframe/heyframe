<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Handler;

use HeyFrame\Core\Framework\Gateway\Context\Command\AbstractContextGatewayCommand;
use HeyFrame\Core\Framework\Gateway\Context\Command\ChangeBillingAddressCommand;
use HeyFrame\Core\Framework\Gateway\Context\Command\ChangeShippingAddressCommand;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @extends AbstractContextGatewayCommandHandler<ChangeBillingAddressCommand|ChangeShippingAddressCommand>
 *
 * @internal
 */
#[Package('framework')]
class ChangeAddressCommandHandler extends AbstractContextGatewayCommandHandler
{
    public function handle(AbstractContextGatewayCommand $command, ChannelContext $context, array &$parameters): void
    {
        if ($command instanceof ChangeBillingAddressCommand) {
            $parameters['billingAddressId'] = $command->addressId;
        }

        if ($command instanceof ChangeShippingAddressCommand) {
            $parameters['shippingAddressId'] = $command->addressId;
        }
    }

    public static function supportedCommands(): array
    {
        return [
            ChangeBillingAddressCommand::class,
            ChangeShippingAddressCommand::class,
        ];
    }
}
