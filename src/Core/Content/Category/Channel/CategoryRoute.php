<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Channel;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Content\Category\CategoryException;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('discovery')]
class CategoryRoute extends AbstractCategoryRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(
        private readonly EntityRepository $categoryRepository,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public static function buildName(string $id): string
    {
        return 'category-route-' . $id;
    }

    public function getDecorated(): AbstractCategoryRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/category/{navigationId}', name: 'front-api.category.detail', methods: ['GET', 'POST'])]
    public function load(string $navigationId, Request $request, ChannelContext $context): CategoryRouteResponse
    {
        $this->cacheTagCollector->addTag(self::buildName($navigationId));

        $category = $this->loadCategory($navigationId, $context->getContext());

        return new CategoryRouteResponse($category);
    }

    private function loadCategory(string $categoryId, Context $context): CategoryEntity
    {
        $criteria = new Criteria([$categoryId]);
        $criteria->setTitle('category::data');

        $criteria->addAssociation('media');
        $criteria->addAssociation('translations');

        $category = $this->categoryRepository->search($criteria, $context)->getEntities()->get($categoryId);
        if (!$category instanceof CategoryEntity) {
            throw CategoryException::categoryNotFound($categoryId);
        }

        return $category;
    }
}
