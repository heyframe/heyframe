<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Admin\Event;

use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommand;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class CommandAclValidationEvent extends Event
{
    /**
     * @param list<string> $missingPrivileges
     */
    public function __construct(
        private array $missingPrivileges,
        private readonly AdminApiSource $source,
        private readonly WriteCommand $command
    ) {
    }

    /**
     * @return list<string>
     */
    public function getMissingPrivileges(): array
    {
        return $this->missingPrivileges;
    }

    public function addMissingPrivilege(string $privilege): void
    {
        $this->missingPrivileges[] = $privilege;
    }

    public function getSource(): AdminApiSource
    {
        return $this->source;
    }

    public function getCommand(): WriteCommand
    {
        return $this->command;
    }
}
