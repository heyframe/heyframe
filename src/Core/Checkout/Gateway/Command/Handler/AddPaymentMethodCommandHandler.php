<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayException;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\AddPaymentMethodCommand;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\ExceptionLogger;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AddPaymentMethodCommandHandler extends AbstractCheckoutGatewayCommandHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<PaymentMethodCollection> $paymentMethodRepository
     */
    public function __construct(
        private readonly EntityRepository $paymentMethodRepository,
        private readonly ExceptionLogger $logger,
    ) {
    }

    public static function supportedCommands(): array
    {
        return [
            AddPaymentMethodCommand::class,
        ];
    }

    /**
     * @param AddPaymentMethodCommand $command
     */
    public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void
    {
        $technicalName = $command->paymentMethodTechnicalName;
        $methods = $response->getAvailablePaymentMethods();

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('technicalName', $technicalName))
            ->addAssociation('appPaymentMethod.app');

        $paymentMethod = $this->paymentMethodRepository->search($criteria, $context->getContext())->getEntities()->first();
        if (!$paymentMethod) {
            $this->logger->logOrThrowException(
                CheckoutGatewayException::handlerException('Payment method "{{ technicalName }}" not found', ['technicalName' => $technicalName])
            );

            return;
        }

        $methods->add($paymentMethod);
    }
}
