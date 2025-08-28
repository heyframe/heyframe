<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SnippetEntity>
 */
#[Package('discovery')]
class SnippetCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'snippet_collection';
    }

    protected function getExpectedClass(): string
    {
        return SnippetEntity::class;
    }
}
