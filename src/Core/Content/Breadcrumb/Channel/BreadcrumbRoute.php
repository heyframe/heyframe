<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Breadcrumb\Channel;

use HeyFrame\Core\Content\Breadcrumb\Struct\BreadcrumbCollection;
use HeyFrame\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use HeyFrame\Core\Content\Product\Exception\ProductNotFoundException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('inventory')]
class BreadcrumbRoute extends AbstractBreadcrumbRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly CategoryBreadcrumbBuilder $breadcrumbBuilder,
    ) {
    }

    public function getDecorated(): AbstractBreadcrumbRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/breadcrumb/{id}', name: 'store-api.breadcrumb', requirements: ['id' => '[0-9a-f]{32}'], methods: ['GET'])]
    public function load(Request $request, ChannelContext $channelContext): BreadcrumbRouteResponse
    {
        $id = $request->get('id', '');
        $type = $request->get('type', 'product');
        if ($type === 'category') {
            $breadcrumb = $this->getCategories($id, $channelContext);
        } else {
            $breadcrumb = $this->tryToGetCategoriesFromProductOrCategory(
                $id,
                $request->get('referrerCategoryId', ''),
                $channelContext
            );
        }

        return new BreadcrumbRouteResponse($breadcrumb);
    }

    private function getCategories(string $id, ChannelContext $channelContext): BreadcrumbCollection
    {
        $category = $this->breadcrumbBuilder->loadCategory($id, $channelContext->getContext());

        if ($category === null) {
            return new BreadcrumbCollection();
        }

        return $this->breadcrumbBuilder->getCategoryBreadcrumbUrls(
            $category,
            $channelContext->getContext(),
            $channelContext->getChannel()
        );
    }

    /**
     * Simple helper function to retry with category type if product is not found
     */
    private function tryToGetCategoriesFromProductOrCategory(string $id, string $referrerCategoryId, ChannelContext $channelContext): BreadcrumbCollection
    {
        try {
            $categories = $this->breadcrumbBuilder->getProductBreadcrumbUrls($id, $referrerCategoryId, $channelContext);
        } catch (ProductNotFoundException) {
            $categories = $this->getCategories($id, $channelContext);
        }

        return $categories;
    }
}
