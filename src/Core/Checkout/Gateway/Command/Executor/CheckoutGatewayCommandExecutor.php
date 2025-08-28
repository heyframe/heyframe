<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Executor;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayException;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\CheckoutGatewayCommandCollection;
use HeyFrame\Core\Checkout\Gateway\Command\Registry\CheckoutGatewayCommandRegistry;
use HeyFrame\Core\Framework\Log\ExceptionLogger;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
final readonly class CheckoutGatewayCommandExecutor
{
    /**
     * @internal
     */
    public function __construct(
        private CheckoutGatewayCommandRegistry $registry,
        private ExceptionLogger $logger,
    ) {
    }

    public function execute(
        CheckoutGatewayCommandCollection $commands,
        CheckoutGatewayResponse $response,
        ChannelContext $context,
    ): CheckoutGatewayResponse {
        foreach ($commands as $command) {
            if (!$this->registry->has($command::getDefaultKeyName())) {
                $this->logger->logOrThrowException(CheckoutGatewayException::handlerNotFound($command::getDefaultKeyName()));
                continue;
            }

            $this->registry->get($command::getDefaultKeyName())->handle($command, $response, $context);
        }

        return $response;
    }
}
