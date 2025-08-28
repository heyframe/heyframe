<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Handler;

use HeyFrame\Core\Checkout\Customer\Channel\AbstractRegisterRoute;
use HeyFrame\Core\Framework\Gateway\Context\Command\AbstractContextGatewayCommand;
use HeyFrame\Core\Framework\Gateway\Context\Command\RegisterCustomerCommand;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @extends AbstractContextGatewayCommandHandler<RegisterCustomerCommand>
 *
 * @internal
 */
#[Package('framework')]
class RegisterCustomerCommandHandler extends AbstractContextGatewayCommandHandler
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractRegisterRoute $registerRoute,
    ) {
    }

    public function handle(AbstractContextGatewayCommand $command, ChannelContext $context, array &$parameters): void
    {
        $data = new RequestDataBag($command->data);
        $response = $this->registerRoute->register($data, $context);

        $parameters['token'] = $response->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN);
    }

    public static function supportedCommands(): array
    {
        return [RegisterCustomerCommand::class];
    }
}
