<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Seo\SeoUrlRoute;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Seo\SeoUrlUpdater;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class SeoUrlUpdateListener implements EventSubscriberInterface
{
    final public const CATEGORY_SEO_URL_UPDATER = 'category.seo-url';
    final public const PRODUCT_SEO_URL_UPDATER = 'product.seo-url';

    /**
     * @internal
     */
    public function __construct(
        private readonly SeoUrlUpdater $seoUrlUpdater,
        private readonly Connection $connection
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
