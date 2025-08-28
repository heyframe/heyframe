<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Computed;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class LockedField extends BoolField
{
    public function __construct()
    {
        parent::__construct('locked', 'locked');

        $this->addFlags(new Computed());
    }
}
