<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomEntity\Xml\Field\Traits;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
trait TranslatableTrait
{
    protected bool $translatable;

    public function isTranslatable(): bool
    {
        return $this->translatable;
    }
}
