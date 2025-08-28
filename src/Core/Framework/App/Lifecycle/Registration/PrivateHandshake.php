<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Registration;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use HeyFrame\Core\Framework\Log\Package;
use Psr\Http\Message\RequestInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class PrivateHandshake implements AppHandshakeInterface
{
    public function __construct(
        private readonly string $shopUrl,
        #[\SensitiveParameter]
        private readonly string $secret,
        private readonly string $appEndpoint,
        private readonly string $appName,
        private readonly string $shopId,
        private readonly string $heyframeVersion
    ) {
    }

    public function assembleRequest(): RequestInterface
    {
        $date = new \DateTime();
        $uri = new Uri($this->appEndpoint);

        $uri = Uri::withQueryValues($uri, [
            'shop-id' => $this->shopId,
            'shop-url' => $this->shopUrl,
            'timestamp' => (string) $date->getTimestamp(),
        ]);

        $signature = hash_hmac('sha256', $uri->getQuery(), $this->secret);

        return new Request(
            'GET',
            $uri,
            [
                'heyframe-app-signature' => $signature,
                'sw-version' => $this->heyframeVersion,
            ]
        );
    }

    public function fetchAppProof(): string
    {
        return hash_hmac('sha256', $this->shopId . $this->shopUrl . $this->appName, $this->secret);
    }
}
