<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\JsonSerializableTrait;

#[Package('framework')]
class ChannelApiSource implements ContextSource, \JsonSerializable
{
    use JsonSerializableTrait;

    public string $type = 'channel';

    private readonly ?string $customerId;

    /**
     * @var array<string>
     */
    private array $permissions = [];

    public function __construct(private readonly string $channelId)
    {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isAllowed(string $privilege): bool
    {
        return \in_array($privilege, $this->permissions, true);
    }

    /**
     * @return array<string>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * @param array<string> $permissions
     */
    public function setPermissions(array $permissions): void
    {
        $this->permissions = $permissions;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }
}
