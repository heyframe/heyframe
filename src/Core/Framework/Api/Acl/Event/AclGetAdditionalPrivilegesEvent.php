<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class AclGetAdditionalPrivilegesEvent extends NestedEvent
{
    public function __construct(
        private readonly Context $context,
        private array $privileges
    ) {
    }

    public function getPrivileges(): array
    {
        return $this->privileges;
    }

    public function setPrivileges(array $privileges): void
    {
        $this->privileges = $privileges;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
