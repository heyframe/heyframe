<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Breadcrumb\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<Breadcrumb>
 */
#[Package('inventory')]
class BreadcrumbCollection extends Collection
{
    public function getApiAlias(): string
    {
        return 'breadcrumb_collection';
    }

    protected function getExpectedClass(): string
    {
        return Breadcrumb::class;
    }
}
