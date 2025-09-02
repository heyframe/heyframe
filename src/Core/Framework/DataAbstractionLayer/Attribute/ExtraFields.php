<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Attribute;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class ExtraFields extends Field
{
    public const TYPE = 'extra-fields';

    public function __construct(public ?string $column = null)
    {
        parent::__construct(type: self::TYPE, api: true, column: $column);
    }
}
