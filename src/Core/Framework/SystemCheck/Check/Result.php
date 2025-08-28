<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\SystemCheck\Check;

/**
 * @codeCoverageIgnore
 */
class Result
{
    /**
     * @param mixed[] $extra
     */
    public function __construct(
        public readonly string $name,
        public readonly Status $status,
        public readonly string $message,
        public readonly ?bool $healthy = null,
        public readonly array $extra = [],
    ) {
    }
}
