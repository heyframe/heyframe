<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Attribute;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class AllowHtml
{
    public function __construct(public bool $sanitized = false)
    {
    }
}
