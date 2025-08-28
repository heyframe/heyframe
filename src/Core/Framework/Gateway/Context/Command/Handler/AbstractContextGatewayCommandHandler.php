<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Handler;

use HeyFrame\Core\Framework\Gateway\Context\Command\AbstractContextGatewayCommand;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @template TCommand of AbstractContextGatewayCommand = AbstractContextGatewayCommand
 *
 * @internal
 */
#[Package('framework')]
abstract class AbstractContextGatewayCommandHandler
{
    /**
     * @param TCommand $command
     * @param array<string, mixed> $parameters
     */
    abstract public function handle(AbstractContextGatewayCommand $command, ChannelContext $context, array &$parameters): void;

    /**
     * @return array<class-string<TCommand>>
     */
    abstract public static function supportedCommands(): array;
}
