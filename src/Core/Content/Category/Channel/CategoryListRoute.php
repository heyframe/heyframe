<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Channel;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('discovery')]
class CategoryListRoute extends AbstractCategoryListRoute
{
    /**
     * @internal
     *
     * @param ChannelRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(private readonly ChannelRepository $categoryRepository)
    {
    }

    public function getDecorated(): AbstractCategoryListRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/category', name: 'store-api.category.search', defaults: ['_entity' => 'category'], methods: ['GET', 'POST'])]
    public function load(Criteria $criteria, ChannelContext $context): CategoryListRouteResponse
    {
        $rootIds = array_filter([
            $context->getChannel()->getNavigationId(),
            $context->getChannel()->getFooterCategoryId(),
            $context->getChannel()->getServiceCategoryId(),
        ]);

        if (!empty($rootIds)) {
            $filter = new OrFilter();

            foreach ($rootIds as $rootId) {
                $filter->addQuery(new EqualsFilter('id', $rootId));
                $filter->addQuery(new ContainsFilter('path', '|' . $rootId . '|'));
            }

            $criteria->addFilter($filter);
        }

        return new CategoryListRouteResponse($this->categoryRepository->search($criteria, $context));
    }
}
