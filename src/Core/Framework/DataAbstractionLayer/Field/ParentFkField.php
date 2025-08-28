<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ParentFkField extends FkField
{
    public function __construct(string $referenceClass)
    {
        parent::__construct('parent_id', 'parentId', $referenceClass);
    }
}
