<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;
use HeyFrame\Core\System\Snippet\Struct\TranslationFile;

/**
 * @internal
 *
 * @extends Collection<TranslationFile>
 */
#[Package('discovery')]
class TranslationFileCollection extends Collection
{
    public function add($element): void
    {
        $this->validateType($element);

        $this->set($element->getFullPath(), $element);
    }

    protected function getExpectedClass(): string
    {
        return TranslationFile::class;
    }
}
