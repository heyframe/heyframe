<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomEntity\Xml\Field;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\CustomEntity\Xml\Field\Traits\RequiredTrait;

/**
 * @internal
 */
#[Package('framework')]
class PriceField extends Field
{
    use RequiredTrait;

    protected string $type = 'price';
}
