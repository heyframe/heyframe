<?php

declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Context;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
final readonly class LanguageInfo
{
    public function __construct(
        public string $name,
        public string $localeCode,
    ) {
    }
}
