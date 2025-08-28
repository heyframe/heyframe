<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomEntity\Xml\Field;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class LabelField extends Field
{
    protected string $type = 'label';
}
