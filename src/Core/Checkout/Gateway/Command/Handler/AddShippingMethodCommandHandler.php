<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command\Handler;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayException;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\AddShippingMethodCommand;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\ExceptionLogger;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
class AddShippingMethodCommandHandler extends AbstractCheckoutGatewayCommandHandler
{
    /**
     * @internal
     *
     * @param EntityRepository<ShippingMethodCollection> $shippingMethodRepository
     */
    public function __construct(
        private readonly EntityRepository $shippingMethodRepository,
        private readonly ExceptionLogger $logger,
    ) {
    }

    public static function supportedCommands(): array
    {
        return [
            AddShippingMethodCommand::class,
        ];
    }

    /**
     * @param AddShippingMethodCommand $command
     */
    public function handle(AbstractCheckoutGatewayCommand $command, CheckoutGatewayResponse $response, ChannelContext $context): void
    {
        $technicalName = $command->shippingMethodTechnicalName;
        $methods = $response->getAvailableShippingMethods();

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('technicalName', $technicalName))
            ->addAssociation('appShippingMethod.app');

        $shippingMethod = $this->shippingMethodRepository->search($criteria, $context->getContext())->getEntities()->first();
        if (!$shippingMethod) {
            $this->logger->logOrThrowException(
                CheckoutGatewayException::handlerException('Shipping method "{{ technicalName }}" not found', ['technicalName' => $technicalName])
            );

            return;
        }

        $methods->add($shippingMethod);
    }
}
