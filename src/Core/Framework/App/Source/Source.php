<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Source;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Filesystem;

/**
 * @internal
 */
#[Package('framework')]
interface Source
{
    public static function name(): string;

    public function supports(AppEntity|Manifest $app): bool;

    public function filesystem(AppEntity|Manifest $app): Filesystem;

    /**
     * @param array<Filesystem> $filesystems
     */
    public function reset(array $filesystems): void;
}
