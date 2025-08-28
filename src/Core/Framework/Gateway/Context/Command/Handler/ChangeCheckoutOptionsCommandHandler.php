<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command\Handler;

use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Gateway\Context\Command\AbstractContextGatewayCommand;
use HeyFrame\Core\Framework\Gateway\Context\Command\ChangePaymentMethodCommand;
use HeyFrame\Core\Framework\Gateway\GatewayException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @extends AbstractContextGatewayCommandHandler<ChangePaymentMethodCommand>
 *
 * @internal
 */
#[Package('framework')]
class ChangeCheckoutOptionsCommandHandler extends AbstractContextGatewayCommandHandler
{
    /**
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     *
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $paymentMethodRepository,
    ) {
    }

    public function handle(AbstractContextGatewayCommand $command, ChannelContext $context, array &$parameters): void
    {
        $technicalName = $command->technicalName;

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));

        if ($command instanceof ChangePaymentMethodCommand) {
            $paymentMethodId = $this->paymentMethodRepository->searchIds($criteria, $context->getContext())->firstId();

            if ($paymentMethodId === null) {
                throw GatewayException::handlerException('Payment method with technical name {{ technicalName }} not found', ['technicalName' => $technicalName]);
            }

            $parameters['paymentMethodId'] = $paymentMethodId;
        }
    }

    public static function supportedCommands(): array
    {
        return [
            ChangePaymentMethodCommand::class,
        ];
    }
}
