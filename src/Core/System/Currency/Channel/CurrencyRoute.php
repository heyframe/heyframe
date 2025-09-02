<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency\Channel;

use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('fundamentals@framework')]
class CurrencyRoute extends AbstractCurrencyRoute
{
    final public const ALL_TAG = 'currency-route';

    /**
     * @internal
     *
     * @param ChannelRepository<CurrencyCollection> $currencyRepository
     */
    public function __construct(
        private readonly ChannelRepository $currencyRepository,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function getDecorated(): AbstractCurrencyRoute
    {
        throw new DecorationPatternException(self::class);
    }

    public static function buildName(string $channelId): string
    {
        return 'currency-route-' . $channelId;
    }

    #[Route(path: '/front-api/currency', name: 'front-api.currency', methods: ['GET', 'POST'], defaults: ['_entity' => 'currency'])]
    public function load(Request $request, ChannelContext $context, Criteria $criteria): CurrencyRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($context->getChannelId()), self::ALL_TAG);

        return new CurrencyRouteResponse($this->currencyRepository->search($criteria, $context)->getEntities());
    }
}
