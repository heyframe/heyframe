<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Handler;

use HeyFrame\Core\Framework\Gateway\Context\Command\AbstractContextGatewayCommand;
use HeyFrame\Core\Framework\Gateway\Context\Command\AddCustomerMessageCommand;
use HeyFrame\Core\Framework\Gateway\GatewayException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @extends AbstractContextGatewayCommandHandler<AddCustomerMessageCommand>
 *
 * @internal
 */
#[Package('framework')]
class AddCustomerMessageCommandHandler extends AbstractContextGatewayCommandHandler
{
    public function handle(AbstractContextGatewayCommand $command, ChannelContext $context, array &$parameters): void
    {
        throw GatewayException::customerMessage($command->message);
    }

    public static function supportedCommands(): array
    {
        return [AddCustomerMessageCommand::class];
    }
}
