<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Processing\Writer;

use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;

#[Package('fundamentals@after-sales')]
abstract class AbstractWriter
{
    /**
     * @param array<string, mixed> $data
     */
    abstract public function append(Config $config, array $data, int $index): void;

    abstract public function flush(Config $config, string $targetPath): void;

    abstract public function finish(Config $config, string $targetPath): void;

    protected function getDecorated(): AbstractWriter
    {
        throw new DecorationPatternException(self::class);
    }
}
