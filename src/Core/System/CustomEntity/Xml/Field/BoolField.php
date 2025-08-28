<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomEntity\Xml\Field;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\CustomEntity\Xml\Field\Traits\RequiredTrait;
use HeyFrame\Core\System\CustomEntity\Xml\Field\Traits\TranslatableTrait;

/**
 * @internal
 */
#[Package('framework')]
class BoolField extends Field
{
    use RequiredTrait;
    use TranslatableTrait;

    protected string $type = 'bool';

    protected ?bool $default = null;

    public function getDefault(): ?bool
    {
        return $this->default;
    }
}
