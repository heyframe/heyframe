<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ShopId;

use HeyFrame\Core\Framework\Log\Package;
use PHPUnit\Framework\Attributes\CodeCoverageIgnore;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
readonly class FingerprintMatch
{
    public function __construct(
        public string $identifier,
        public string $storedStamp,
        public int $score,
    ) {
    }
}
