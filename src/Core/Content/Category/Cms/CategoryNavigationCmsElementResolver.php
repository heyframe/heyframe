<?php

declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Cms;

use HeyFrame\Core\Content\Category\Service\NavigationLoaderInterface;
use HeyFrame\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use HeyFrame\Core\Content\Cms\DataResolver\CriteriaCollection;
use HeyFrame\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use HeyFrame\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use HeyFrame\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class CategoryNavigationCmsElementResolver extends AbstractCmsElementResolver
{
    /**
     * @internal
     */
    public function __construct(
        private readonly NavigationLoaderInterface $navigationLoader,
    ) {
    }

    /**
     * @codeCoverageIgnore
     */
    public function getType(): string
    {
        return 'category-navigation';
    }

    /**
     * @codeCoverageIgnore
     */
    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $channelContext = $resolverContext->getChannelContext();
        $channel = $channelContext->getChannel();

        $rootNavigationId = $channel->getNavigationCategoryId();
        $navigationId = $resolverContext->getRequest()->get('navigationId', $rootNavigationId);

        $tree = $this->navigationLoader->load(
            $navigationId,
            $channelContext,
            $rootNavigationId,
            $channel->getNavigationCategoryDepth()
        );

        $slot->setData($tree);
    }
}
