<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\ContentLayout;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ContentLayoutEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected string $version;

    /**
     * @var array<string, mixed>
     */
    protected array $structure;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $schema = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @return array<string, mixed>
     */
    public function getStructure(): array
    {
        return $this->structure;
    }

    /**
     * @param array<string, mixed> $structure
     */
    public function setStructure(array $structure): void
    {
        $this->structure = $structure;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSchema(): ?array
    {
        return $this->schema;
    }

    /**
     * @param array<string, mixed>|null $schema
     */
    public function setSchema(?array $schema): void
    {
        $this->schema = $schema;
    }
}
