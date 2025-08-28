<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\DataTransfer\SnippetPath;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('discovery')]
readonly class SnippetPath
{
    public function __construct(
        public string $location,
        public bool $isLocal = false,
    ) {
    }
}
