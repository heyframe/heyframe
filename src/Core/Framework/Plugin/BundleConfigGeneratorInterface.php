<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @phpstan-type BundleConfig array{
 *         basePath: string,
 *         views: string[],
 *         technicalName: string,
 *         administration?: array{
 *             path: string,
 *             entryFilePath: string|null,
 *             webpack: string|null,
 *         },
 *         frontend: array{
 *            path: string ,
 *            entryFilePath: string|null,
 *            webpack: string|null,
 *            styleFiles: string[],
 *         }
 *     }
 */
#[Package('framework')]
interface BundleConfigGeneratorInterface
{
    /**
     * Returns the bundle config for the webpack plugin injector
     *
     * @return array<string, BundleConfig>
     */
    public function getConfig(): array;
}
