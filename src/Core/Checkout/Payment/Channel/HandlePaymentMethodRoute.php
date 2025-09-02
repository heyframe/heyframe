<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Channel;

use HeyFrame\Core\Checkout\Payment\PaymentException;
use HeyFrame\Core\Checkout\Payment\PaymentProcessor;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceInterface;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceParameters;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class HandlePaymentMethodRoute extends AbstractHandlePaymentMethodRoute
{
    /**
     * @param EntityRepository<CurrencyCollection> $currencyRepository
     *
     * @internal
     */
    public function __construct(
        private readonly PaymentProcessor $paymentProcessor,
        private readonly DataValidator $dataValidator,
        private readonly ChannelContextServiceInterface $contextService,
        private readonly EntityRepository $currencyRepository,
    ) {
    }

    public function getDecorated(): AbstractHandlePaymentMethodRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/handle-payment', name: 'front-api.payment.handle', methods: ['GET', 'POST'])]
    public function load(Request $request, ChannelContext $context): HandlePaymentMethodRouteResponse
    {
        $data = [...$request->query->all(), ...$request->request->all()];
        $this->dataValidator->validate($data, $this->createDataValidation());
        /** @var array{orderId: string, finishUrl: ?string, errorUrl: ?string} $data */
        $orderCurrencyId = $this->getCurrencyFromOrder($data['orderId'], $context->getContext());

        if ($context->getCurrencyId() !== $orderCurrencyId) {
            $context = $this->contextService->get(
                new ChannelContextServiceParameters(
                    $context->getChannelId(),
                    $context->getToken(),
                    $context->getLanguageId(),
                    $orderCurrencyId,
                )
            );
        }

        $response = $this->paymentProcessor->pay(
            $data['orderId'],
            $request,
            $context,
            $data['finishUrl'] ?? null,
            $data['errorUrl'] ?? null,
        );

        return new HandlePaymentMethodRouteResponse($response);
    }

    private function createDataValidation(): DataValidationDefinition
    {
        return (new DataValidationDefinition())
            ->add('orderId', new NotBlank(), new Type('string'))
            ->add('finishUrl', new Type('string'))
            ->add('errorUrl', new Type('string'));
    }

    private function getCurrencyFromOrder(string $orderId, Context $context): string
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('orders.id', $orderId));

        $id = $this->currencyRepository->searchIds($criteria, $context)->firstId();
        if (!$id) {
            throw PaymentException::invalidOrder($orderId);
        }

        return $id;
    }
}
