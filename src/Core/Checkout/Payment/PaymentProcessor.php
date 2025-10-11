<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use HeyFrame\Core\Checkout\Payment\Cart\AbstractPaymentTransactionStructFactory;
use HeyFrame\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use HeyFrame\Core\Checkout\Payment\Cart\Token\TokenFactoryInterfaceV2;
use HeyFrame\Core\Checkout\Payment\Cart\Token\TokenStruct;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\StateMachine\Loader\InitialStateIdLoader;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[Package('checkout')]
class PaymentProcessor
{
    /**
     * @param EntityRepository<OrderTransactionCollection> $orderTransactionRepository
     *
     * @internal
     */
    public function __construct(
        private readonly TokenFactoryInterfaceV2 $tokenFactory,
        private readonly PaymentHandlerRegistry $paymentHandlerRegistry,
        private readonly EntityRepository $orderTransactionRepository,
        private readonly OrderTransactionStateHandler $transactionStateHandler,
        private readonly LoggerInterface $logger,
        private readonly AbstractPaymentTransactionStructFactory $paymentTransactionStructFactory,
        private readonly InitialStateIdLoader $initialStateIdLoader,
        private readonly RouterInterface $router,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function pay(
        string $orderId,
        Request $request,
        ChannelContext $channelContext,
        ?string $finishUrl = null,
        ?string $errorUrl = null,
    ): RedirectResponse|ArrayStruct|null {
        $transaction = $this->getCurrentOrderTransaction($orderId, $channelContext->getContext());
        if (!$transaction) {
            return null;
        }
        $token = $this->getToken($transaction, $finishUrl, $errorUrl, $channelContext);
        $returnUrl = $this->getReturnUrl($token);

        try {
            $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($transaction->getPaymentMethodId());
            if (!$paymentHandler) {
                throw PaymentException::unknownPaymentMethodById($transaction->getPaymentMethodId());
            }

            $transactionStruct = $this->paymentTransactionStructFactory->build($transaction->getId(), $channelContext->getContext(), $returnUrl);
            $validationStruct = $transaction->getValidationData() ? new ArrayStruct($transaction->getValidationData()) : null;

            $response = $paymentHandler->pay($request, $transactionStruct, $channelContext->getContext(), $validationStruct);
            if ($response instanceof RedirectResponse) {
                $token = null;
            }

            return $response;
        } catch (\Throwable $e) {
            $this->logger->error('An error occurred during processing the payment', ['orderTransactionId' => $transaction->getId(), 'exceptionMessage' => $e->getMessage(), 'exceptionTrace' => $e->getTraceAsString(), 'exception' => $e]);
            $this->transactionStateHandler->fail($transaction->getId(), $channelContext->getContext());
            if ($errorUrl !== null) {
                $errorCode = $e instanceof HttpException ? $e->getErrorCode() : PaymentException::PAYMENT_PROCESS_ERROR;

                return new RedirectResponse(\sprintf('%s%serror-code=%s', $errorUrl, parse_url($errorUrl, \PHP_URL_QUERY) ? '&' : '?', $errorCode));
            }

            throw $e;
        } finally {
            if ($token) {
                // has been nulled, if response is RedirectResponse, therefore we have a finalize step
                $this->tokenFactory->invalidateToken($token);
            }
        }
    }

    public function finalize(TokenStruct $token, Request $request, ChannelContext $context): TokenStruct
    {
        if ($token->getPaymentMethodId() === null || $token->getTransactionId() === null) {
            throw PaymentException::invalidToken($token->getToken() ?? '');
        }

        $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($token->getPaymentMethodId());
        if (!$paymentHandler) {
            throw PaymentException::unknownPaymentMethodById($token->getPaymentMethodId());
        }

        try {
            $transactionStruct = $this->paymentTransactionStructFactory->build($token->getTransactionId(), $context->getContext());
            $paymentHandler->finalize($request, $transactionStruct, $context->getContext());
        } catch (\Throwable $e) {
            if ($e instanceof PaymentException && $e->getErrorCode() === PaymentException::PAYMENT_CUSTOMER_CANCELED_EXTERNAL) {
                $this->transactionStateHandler->cancel($token->getTransactionId(), $context->getContext());
            } else {
                $this->logger->error('An error occurred during finalizing async payment', ['orderTransactionId' => $token->getTransactionId(), 'exceptionMessage' => $e->getMessage(), 'exception' => $e]);
                $this->transactionStateHandler->fail($token->getTransactionId(), $context->getContext());
            }

            $token->setException($e);
        } finally {
            if ($token->getToken() !== null) {
                $this->tokenFactory->invalidateToken($token->getToken());
            }
        }

        return $token;
    }

    public function validate(
        Cart $cart,
        RequestDataBag $dataBag,
        ChannelContext $channelContext
    ): ?Struct {
        try {
            $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($channelContext->getPaymentMethod()->getId());
            if (!$paymentHandler) {
                throw PaymentException::unknownPaymentMethodById($channelContext->getPaymentMethod()->getId());
            }

            $struct = $paymentHandler->validate($cart, $dataBag, $channelContext);
            $cart->getTransactions()->first()?->setValidationStruct($struct);

            return $struct;
        } catch (\Throwable $e) {
            $this->logger->error(
                'An error occurred during processing the validation of the payment. The order has not been placed yet.',
                ['customerId' => $channelContext->getCustomerId(), 'exceptionMessage' => $e->getMessage(), 'exception' => $e]
            );

            throw $e;
        }
    }

    private function getCurrentOrderTransaction(string $orderId, Context $context): ?OrderTransactionEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('stateId', $this->initialStateIdLoader->get(OrderTransactionStates::STATE_MACHINE)))
            ->addFilter(new EqualsFilter('orderId', $orderId))
            ->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING))
            ->setLimit(1);

        $transaction = $this->orderTransactionRepository->search($criteria, $context)->getEntities()->first();

        if (!$transaction) {
            // check, if there are no transactions at all or just not with non-initial state
            $criteria->resetFilters();
            $criteria->addFilter(new EqualsFilter('orderId', $orderId));

            if ($this->orderTransactionRepository->searchIds($criteria, $context)->firstId()) {
                return null;
            }

            throw PaymentException::invalidOrder($orderId);
        }

        return $transaction;
    }

    private function getToken(OrderTransactionEntity $transaction, ?string $finishUrl, ?string $errorUrl, ChannelContext $channelContext): string
    {
        $paymentFinalizeTransactionTime = $this->systemConfigService->get('core.cart.paymentFinalizeTransactionTime', $channelContext->getChannelId());

        $paymentFinalizeTransactionTime = \is_numeric($paymentFinalizeTransactionTime)
            ? (int) $paymentFinalizeTransactionTime * 60
            : null;

        $tokenStruct = new TokenStruct(
            null,
            null,
            $transaction->getPaymentMethodId(),
            $transaction->getId(),
            $finishUrl,
            $paymentFinalizeTransactionTime,
            $errorUrl
        );

        return $this->tokenFactory->generateToken($tokenStruct);
    }

    private function getReturnUrl(string $token): string
    {
        $parameter = ['_sw_payment_token' => $token];

        return $this->router->generate('payment.finalize.transaction', $parameter, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
