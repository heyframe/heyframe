<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Api;

use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Exception\ShopIdChangeSuggestedException;
use HeyFrame\Core\Framework\App\ShopId\ShopIdProvider;
use HeyFrame\Core\Framework\App\ShopIdChangeResolver\Resolver;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('framework')]
class ShopIdController extends AbstractController
{
    /**
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly Resolver $shopIdChangeResolver,
        private readonly ShopIdProvider $shopIdProvider,
        private readonly EntityRepository $appRepository,
    ) {
    }

    #[Route(path: 'api/app-system/shop-id/change-strategies', name: 'api.app_system.shop_id.change_strategies', methods: ['GET'])]
    public function getAvailableStrategies(): JsonResponse
    {
        return new JsonResponse($this->shopIdChangeResolver->getAvailableStrategies());
    }

    #[Route(path: 'api/app-system/shop-id/change', name: 'api.app_system.shop_id.change', methods: ['POST'])]
    public function changeShopId(Request $request, Context $context): Response
    {
        $strategy = $request->get('strategy');

        if (!$strategy) {
            throw AppException::missingRequestParameter('strategy');
        }

        $this->shopIdChangeResolver->resolve($strategy, $context);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: 'api/app-system/shop-id/check', name: 'api.app_system.shop_id.check', methods: ['POST'])]
    public function checkShopId(Context $context): Response
    {
        try {
            $this->shopIdProvider->getShopId();
        } catch (ShopIdChangeSuggestedException $e) {
            return new JsonResponse([
                'apps' => $this->appsRegisteredAtAppServers($context),
                'fingerprints' => $e->comparisonResult,
            ]);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @return list<string>
     */
    private function appsRegisteredAtAppServers(Context $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new NotEqualsFilter('appSecret', null));

        $apps = $this->appRepository
            ->search($criteria, $context)
            ->getEntities()
            ->map(function (AppEntity $app) {
                return $app->getTranslation('label');
            });

        return array_values($apps);
    }
}
