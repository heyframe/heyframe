<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Authentication\StoreRequestOptionsProvider;
use HeyFrame\Core\Framework\Store\Exception\ShopSecretInvalidException;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
#[Package('checkout')]
class ShopSecretInvalidMiddleware implements MiddlewareInterface
{
    private const INVALID_SHOP_SECRET = 'HeyFramePlatformException-68';

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function __invoke(ResponseInterface $response, RequestInterface $request): ResponseInterface
    {
        if ($response->getStatusCode() !== 401) {
            return $response;
        }

        $body = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        $code = $body['code'] ?? null;

        if ($code !== self::INVALID_SHOP_SECRET) {
            $response->getBody()->rewind();

            return $response;
        }

        $this->connection->executeStatement('UPDATE user SET store_token = NULL');

        $this->systemConfigService->delete(StoreRequestOptionsProvider::CONFIG_KEY_STORE_SHOP_SECRET);

        throw new ShopSecretInvalidException();
    }
}
