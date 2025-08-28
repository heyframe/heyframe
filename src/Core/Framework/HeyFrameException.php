<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface HeyFrameException extends \Throwable
{
    public function getErrorCode(): string;

    /**
     * @return array<string|int, mixed|null>
     */
    public function getParameters(): array;
}
