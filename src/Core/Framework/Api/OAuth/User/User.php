<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\OAuth\User;

use HeyFrame\Core\Framework\Log\Package;
use League\OAuth2\Server\Entities\UserEntityInterface;

#[Package('framework')]
class User implements UserEntityInterface
{
    /**
     * @param non-empty-string $userId
     */
    public function __construct(private readonly string $userId)
    {
    }

    /**
     * Return the user's identifier.
     *
     * @return non-empty-string
     */
    public function getIdentifier(): string
    {
        return $this->userId;
    }
}
