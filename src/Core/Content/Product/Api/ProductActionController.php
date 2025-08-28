<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Api;

use HeyFrame\Core\Content\Product\Util\VariantCombinationLoader;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('inventory')]
class ProductActionController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(private readonly VariantCombinationLoader $combinationLoader)
    {
    }

    #[Route(path: '/api/_action/product/{productId}/combinations', name: 'api.action.product.combinations', methods: ['GET'])]
    public function getCombinations(string $productId, Context $context): JsonResponse
    {
        return new JsonResponse(
            $this->combinationLoader->load($productId, $context)
        );
    }
}
