<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 *
 * @extends EntityCollection<ScriptEntity>
 */
#[Package('framework')]
class ScriptCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ScriptEntity::class;
    }
}
