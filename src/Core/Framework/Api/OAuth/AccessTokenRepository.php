<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\OAuth;

use HeyFrame\Core\Framework\Api\OAuth\Client\ApiClient;
use HeyFrame\Core\Framework\Log\Package;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

#[Package('framework')]
class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * {@inheritdoc}
     *
     * @param non-empty-string|null $userIdentifier
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, ?string $userIdentifier = null): AccessTokenEntityInterface
    {
        $token = new AccessToken($clientEntity, $scopes, $userIdentifier);

        if ($clientEntity instanceof ApiClient && $clientEntity->getIdentifier() === 'administration') {
            $token->setIdentifier('administration');
        }

        return $token;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAccessToken(string $tokenId): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isAccessTokenRevoked(string $tokenId): bool
    {
        return false;
    }
}
