<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class TreeBreadcrumbField extends JsonField
{
    public function __construct(
        string $storageName = 'breadcrumb',
        string $propertyName = 'breadcrumb',
        private readonly string $nameField = 'name'
    ) {
        parent::__construct($storageName, $propertyName);

        $this->addFlags(new WriteProtected(Context::SYSTEM_SCOPE));
    }

    public function getNameField(): string
    {
        return $this->nameField;
    }
}
