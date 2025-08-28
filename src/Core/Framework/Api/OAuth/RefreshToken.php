<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\OAuth;

use HeyFrame\Core\Framework\Log\Package;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

#[Package('framework')]
class RefreshToken implements RefreshTokenEntityInterface
{
    use EntityTrait;
    use RefreshTokenTrait;
}
