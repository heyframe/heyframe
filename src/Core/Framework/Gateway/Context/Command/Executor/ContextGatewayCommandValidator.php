<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Executor;

use HeyFrame\Core\Framework\Gateway\Context\Command\ContextGatewayCommandCollection;
use HeyFrame\Core\Framework\Gateway\GatewayException;
use HeyFrame\Core\Framework\Log\ExceptionLogger;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal
 */
#[Package('framework')]
class ContextGatewayCommandValidator
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ExceptionLogger $logger,
    ) {
    }

    public function validate(ContextGatewayCommandCollection $commands, ChannelContext $context): void
    {
        if ($commands->getTokenCommands()->count() > 1) {
            $this->logger->logOrThrowException(GatewayException::commandValidationFailed('Only one register or login command is allowed'));

            return;
        }

        $types = $commands->getCommandTypes();

        if (\count($types) !== \count(\array_unique($types))) {
            $this->logger->logOrThrowException(GatewayException::commandValidationFailed('Duplicate commands of a type are not allowed'));
        }
    }
}
