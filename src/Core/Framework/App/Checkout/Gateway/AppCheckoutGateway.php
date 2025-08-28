<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Checkout\Gateway;

use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayException;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayInterface;
use HeyFrame\Core\Checkout\Gateway\CheckoutGatewayResponse;
use HeyFrame\Core\Checkout\Gateway\Command\AbstractCheckoutGatewayCommand;
use HeyFrame\Core\Checkout\Gateway\Command\CheckoutGatewayCommandCollection;
use HeyFrame\Core\Checkout\Gateway\Command\Event\CheckoutGatewayCommandsCollectedEvent;
use HeyFrame\Core\Checkout\Gateway\Command\Executor\CheckoutGatewayCommandExecutor;
use HeyFrame\Core\Checkout\Gateway\Command\Registry\CheckoutGatewayCommandRegistry;
use HeyFrame\Core\Checkout\Gateway\Command\Struct\CheckoutGatewayPayloadStruct;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodEntity;
use HeyFrame\Core\Framework\App\ActiveAppsLoader;
use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\Checkout\Payload\AppCheckoutGatewayPayload;
use HeyFrame\Core\Framework\App\Checkout\Payload\AppCheckoutGatewayPayloadService;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\Log\ExceptionLogger;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('checkout')]
class AppCheckoutGateway implements CheckoutGatewayInterface
{
    /**
     * @param EntityRepository<AppCollection> $appRepository
     *
     * @internal
     */
    public function __construct(
        private readonly AppCheckoutGatewayPayloadService $payloadService,
        private readonly CheckoutGatewayCommandExecutor $executor,
        private readonly CheckoutGatewayCommandRegistry $registry,
        private readonly EntityRepository $appRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ExceptionLogger $logger,
        private readonly ActiveAppsLoader $activeAppsLoader
    ) {
    }

    public function process(CheckoutGatewayPayloadStruct $payload): CheckoutGatewayResponse
    {
        $collected = new CheckoutGatewayCommandCollection();

        $context = $payload->getChannelContext();
        $paymentMethods = $payload->getPaymentMethods()->map(fn (PaymentMethodEntity $paymentMethod) => $paymentMethod->getTechnicalName());
        $shippingMethods = $payload->getShippingMethods()->map(fn (ShippingMethodEntity $shippingMethod) => $shippingMethod->getTechnicalName());

        $appPayload = new AppCheckoutGatewayPayload($context, $payload->getCart(), $paymentMethods, $shippingMethods);
        $apps = $this->getActiveAppsWithCheckoutGateway($context->getContext());

        foreach ($apps as $app) {
            $checkoutGatewayUrl = $app->getCheckoutGatewayUrl();
            \assert(\is_string($checkoutGatewayUrl));
            $appResponse = $this->payloadService->request($checkoutGatewayUrl, $appPayload, $app);

            if (!$appResponse) {
                $this->logger->logOrThrowException(CheckoutGatewayException::emptyAppResponse($app->getName()));
                continue;
            }

            $this->collectCommandsFromAppResponse($appResponse, $collected);
        }

        $response = new CheckoutGatewayResponse(
            $payload->getPaymentMethods(),
            $payload->getShippingMethods(),
            $payload->getCart()->getErrors()
        );

        $this->eventDispatcher->dispatch(new CheckoutGatewayCommandsCollectedEvent($payload, $collected));

        return $this->executor->execute($collected, $response, $context);
    }

    private function getActiveAppsWithCheckoutGateway(Context $context): AppCollection
    {
        // If no active apps are available, we can return early
        if ($this->activeAppsLoader->getActiveApps() === []) {
            return new AppCollection();
        }

        $criteria = new Criteria();
        $criteria->addAssociation('paymentMethods');

        $criteria->addFilter(
            new EqualsFilter('active', true),
            new NotEqualsFilter('checkoutGatewayUrl', null),
        );

        return $this->appRepository->search($criteria, $context)->getEntities();
    }

    private function collectCommandsFromAppResponse(AppCheckoutGatewayResponse $commands, CheckoutGatewayCommandCollection $collected): void
    {
        foreach ($commands->getCommands() as $payload) {
            if (!isset($payload['command'], $payload['payload'])) {
                $this->logger->logOrThrowException(CheckoutGatewayException::payloadInvalid($payload['command'] ?? null));

                continue;
            }

            $commandKey = $payload['command'];

            if (!$this->registry->hasAppCommand($commandKey)) {
                $this->logger->logOrThrowException(CheckoutGatewayException::handlerNotFound($commandKey));

                continue;
            }

            $command = $this->registry->getAppCommand($commandKey);

            if (!\is_a($command, AbstractCheckoutGatewayCommand::class, true)) {
                $this->logger->logOrThrowException(CheckoutGatewayException::handlerNotFound($commandKey));

                continue;
            }

            $commandPayload = $payload['payload'];

            try {
                $executableCommand = $command::createFromPayload($commandPayload);
            } catch (\Error) {
                $this->logger->logOrThrowException(CheckoutGatewayException::payloadInvalid($payload['command']));
                continue;
            }

            $collected->add($executableCommand);
        }
    }
}
