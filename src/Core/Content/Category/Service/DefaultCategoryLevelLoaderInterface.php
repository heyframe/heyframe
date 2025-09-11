<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Service;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @internal only for internal use as it only loads the default category levels
 * externals should rely on the @see NavigationLoader
 */
#[Package('discovery')]
interface DefaultCategoryLevelLoaderInterface
{
    public function loadLevels(
        string $rootId,
        int $rootLevel,
        ChannelContext $context,
        Criteria $criteria,
        int $depth,
    ): CategoryCollection;
}
