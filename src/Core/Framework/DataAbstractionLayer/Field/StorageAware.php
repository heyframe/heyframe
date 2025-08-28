<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface StorageAware
{
    public function getStorageName(): string;
}
