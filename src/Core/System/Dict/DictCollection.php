<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DictEntity>
 */
#[Package('framework')]
class DictCollection extends EntityCollection
{
    public function filterKey(string $key): DictEntity
    {
        $dict = $this->filter(
            fn (DictEntity $dict) => $dict->getKey() === $key
        )->first();

        if ($dict === null) {
            throw DictException::dictNotFound($key);
        }

        return $dict;
    }

    public function getApiAlias(): string
    {
        return 'dict_collection';
    }

    protected function getExpectedClass(): string
    {
        return DictEntity::class;
    }
}
