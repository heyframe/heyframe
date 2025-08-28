<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class TreePathField extends LongTextField
{
    public function __construct(
        string $storageName,
        string $propertyName,
        private readonly string $pathField = 'id'
    ) {
        parent::__construct($storageName, $propertyName);

        $this->addFlags(new WriteProtected(Context::SYSTEM_SCOPE));
    }

    public function getPathField(): string
    {
        return $this->pathField;
    }
}
