<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Write\Command;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
trait ChangeSetAwareTrait
{
    protected bool $requireChangeSet = false;

    protected ?ChangeSet $changeSet = null;

    public function requiresChangeSet(): bool
    {
        return $this->requireChangeSet;
    }

    public function requestChangeSet(): void
    {
        $this->requireChangeSet = true;
    }

    public function getChangeSet(): ?ChangeSet
    {
        return $this->changeSet;
    }

    public function setChangeSet(?ChangeSet $changeSet): void
    {
        $this->changeSet = $changeSet;
    }
}
