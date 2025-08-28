<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Aggregate\SnippetSet;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SnippetSetEntity>
 */
#[Package('discovery')]
class SnippetSetCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'snippet_set_collection';
    }

    protected function getExpectedClass(): string
    {
        return SnippetSetEntity::class;
    }
}
