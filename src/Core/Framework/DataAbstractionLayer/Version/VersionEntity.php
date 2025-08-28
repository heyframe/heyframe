<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Version;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\Version\Aggregate\VersionCommit\VersionCommitCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class VersionEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected VersionCommitCollection $commits;

    public function __construct()
    {
        $this->commits = new VersionCommitCollection();
    }

    public function getCommits(): VersionCommitCollection
    {
        return $this->commits;
    }

    public function setCommits(VersionCommitCollection $commits): void
    {
        $this->commits = $commits;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
