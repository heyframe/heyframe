<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Channel;

use HeyFrame\Core\Checkout\Payment\Hook\PaymentMethodRouteHook;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Rule\RuleIdMatcher;
use HeyFrame\Core\Framework\Script\Execution\ScriptExecutor;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class PaymentMethodRoute extends AbstractPaymentMethodRoute
{
    final public const ALL_TAG = 'payment-method-route';

    /**
     * @internal
     *
     * @param ChannelRepository<PaymentMethodCollection> $paymentMethodRepository
     */
    public function __construct(
        private readonly ChannelRepository $paymentMethodRepository,
        private readonly CacheTagCollector $cacheTagCollector,
        private readonly ScriptExecutor $scriptExecutor,
        private readonly RuleIdMatcher $ruleIdMatcher,
    ) {
    }

    public function getDecorated(): AbstractPaymentMethodRoute
    {
        throw new DecorationPatternException(self::class);
    }

    public static function buildName(string $channelId): string
    {
        return 'payment-method-route-' . $channelId;
    }

    #[Route(
        path: '/front-api/payment-method',
        name: 'front-api.payment.method',
        defaults: ['_entity' => 'payment_method'],
        methods: ['GET', 'POST']
    )]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): PaymentMethodRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($context->getChannelId()));

        $criteria
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('position'))
            ->addAssociation('media');

        $result = $this->paymentMethodRepository->search($criteria, $context);

        $paymentMethods = $result->getEntities();
        $paymentMethods->sortPaymentMethodsByPreference($context);

        if ($request->query->getBoolean('onlyAvailable') || $request->request->getBoolean('onlyAvailable')) {
            $paymentMethods = $this->ruleIdMatcher->filterCollection($paymentMethods, $context->getRuleIds());
        }

        $result->assign(['entities' => $paymentMethods, 'elements' => $paymentMethods->getElements(), 'total' => $paymentMethods->count()]);

        $this->scriptExecutor->execute(new PaymentMethodRouteHook(
            $paymentMethods,
            $request->query->getBoolean('onlyAvailable') || $request->request->getBoolean('onlyAvailable'),
            $context,
        ));

        return new PaymentMethodRouteResponse($result);
    }
}
