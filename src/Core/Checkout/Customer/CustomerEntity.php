<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class CustomerEntity extends Entity implements \Stringable
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $ncikname;

    public function __toString()
    {
        return $this->getNcikname();
    }

    public function getNcikname(): string
    {
        return $this->ncikname;
    }

    public function setNcikname(string $ncikname): void
    {
        $this->ncikname = $ncikname;
    }
}
