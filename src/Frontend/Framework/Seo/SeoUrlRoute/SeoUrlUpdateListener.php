<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Seo\SeoUrlRoute;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Content\Navigation\NavigationEvents;
use HeyFrame\Core\Content\Navigation\Event\NavigationIndexerEvent;
use HeyFrame\Core\Content\LandingPage\Event\LandingPageIndexerEvent;
use HeyFrame\Core\Content\LandingPage\LandingPageEvents;
use HeyFrame\Core\Content\Product\Events\ProductIndexerEvent;
use HeyFrame\Core\Content\Product\ProductEvents;
use HeyFrame\Core\Content\Seo\SeoUrlUpdater;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class SeoUrlUpdateListener implements EventSubscriberInterface
{
    final public const CATEGORY_SEO_URL_UPDATER = 'navigation.seo-url';
    final public const PRODUCT_SEO_URL_UPDATER = 'product.seo-url';
    final public const LANDING_PAGE_SEO_URL_UPDATER = 'landing_page.seo-url';

    /**
     * @internal
     */
    public function __construct(
        private readonly SeoUrlUpdater $seoUrlUpdater,
        private readonly Connection $connection
    ) {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductEvents::PRODUCT_INDEXER_EVENT => 'updateProductUrls',
            NavigationEvents::CATEGORY_INDEXER_EVENT => 'updateNavigationUrls',
            LandingPageEvents::LANDING_PAGE_INDEXER_EVENT => 'updateLandingPageUrls',
        ];
    }

    public function updateNavigationUrls(NavigationIndexerEvent $event): void
    {
        if (\in_array(self::CATEGORY_SEO_URL_UPDATER, $event->getSkip(), true)) {
            return;
        }

        $ids = array_values($event->getIds());

        if (!$event->isFullIndexing) {
            $ids = array_merge($ids, $this->getNavigationChildren($ids));
        }

        $this->seoUrlUpdater->update(NavigationPageSeoUrlRoute::ROUTE_NAME, $ids);
    }

    public function updateProductUrls(ProductIndexerEvent $event): void
    {
        if (\in_array(self::PRODUCT_SEO_URL_UPDATER, $event->getSkip(), true)) {
            return;
        }

        $this->seoUrlUpdater->update(ProductPageSeoUrlRoute::ROUTE_NAME, array_values($event->getIds()));
    }

    public function updateLandingPageUrls(LandingPageIndexerEvent $event): void
    {
        if (\in_array(self::LANDING_PAGE_SEO_URL_UPDATER, $event->getSkip(), true)) {
            return;
        }

        $this->seoUrlUpdater->update(LandingPageSeoUrlRoute::ROUTE_NAME, array_values($event->getIds()));
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string>
     */
    private function getNavigationChildren(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $query = $this->connection->createQueryBuilder();

        $query->select('navigation.id');
        $query->from('navigation');

        foreach ($ids as $id) {
            $key = 'id' . $id;
            $query->orWhere('navigation.type != :type AND navigation.path LIKE :' . $key);
            $query->setParameter($key, '%' . $id . '%');
        }

        $query->setParameter('type', NavigationDefinition::TYPE_LINK);

        $children = $query->executeQuery()->fetchFirstColumn();

        if (!$children) {
            return [];
        }

        $ids = Uuid::fromBytesToHexList($children);

        return $ids;
    }
}
