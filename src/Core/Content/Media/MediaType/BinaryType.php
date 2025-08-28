<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\MediaType;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class BinaryType extends MediaType
{
    protected string $name = 'BINARY';
}
