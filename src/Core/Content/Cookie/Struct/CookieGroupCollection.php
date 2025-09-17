<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Cookie\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 *  Collection of {@see CookieGroup} indexed by the group's technicalName
 *
 * @extends Collection<CookieGroup>
 */
#[Package('framework')]
class CookieGroupCollection extends Collection
{
    public function set($key, $element): void
    {
        parent::set($element->getTechnicalName(), $element);
    }

    public function add($element): void
    {
        $this->validateType($element);

        parent::set($element->getTechnicalName(), $element);
    }

    public function getApiAlias(): string
    {
        return 'cookie_group_collection';
    }

    protected function getExpectedClass(): string
    {
        return CookieGroup::class;
    }
}
