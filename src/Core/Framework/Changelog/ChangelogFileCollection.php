<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Changelog;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @internal
 *
 * @extends Collection<ChangelogFile>
 */
#[Package('framework')]
class ChangelogFileCollection extends Collection
{
    protected function getExpectedClass(): ?string
    {
        return ChangelogFile::class;
    }
}
