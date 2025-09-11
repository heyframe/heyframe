<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Service;

use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('discovery')]
abstract class AbstractCategoryUrlGenerator
{
    abstract public function getDecorated(): AbstractCategoryUrlGenerator;

    abstract public function generate(CategoryEntity $category, ?ChannelEntity $channel): ?string;
}
