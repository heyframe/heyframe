<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class StoreLicenseViolationStruct extends Struct
{
    protected string $name;

    protected StoreLicenseViolationTypeStruct $type;

    protected string $text;

    /**
     * @var StoreActionStruct[]
     */
    protected array $actions;

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): StoreLicenseViolationTypeStruct
    {
        return $this->type;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getApiAlias(): string
    {
        return 'store_license_violation';
    }
}
