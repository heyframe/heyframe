<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\File;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
interface FileUrlValidatorInterface
{
    public function isValid(string $source): bool;
}
