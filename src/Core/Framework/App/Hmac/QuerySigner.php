<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Hmac;

use GuzzleHttp\Psr7\Uri;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\AppException;
use HeyFrame\Core\Framework\App\Hmac\Guzzle\AuthMiddleware;
use HeyFrame\Core\Framework\App\ShopId\ShopIdProvider;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Authentication\LocaleProvider;
use HeyFrame\Core\Framework\Store\InAppPurchase;
use Psr\Http\Message\UriInterface;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class QuerySigner
{
    public function __construct(
        private readonly string $shopUrl,
        private readonly string $heyframeVersion,
        private readonly LocaleProvider $localeProvider,
        private readonly ShopIdProvider $shopIdProvider,
        private readonly InAppPurchase $inAppPurchase,
    ) {
    }

    public function signUri(string $uri, AppEntity $app, Context $context): UriInterface
    {
        $secret = $app->getAppSecret();
        if ($secret === null) {
            throw AppException::appSecretMissing($app->getName());
        }

        $unsignedUri = Uri::withQueryValues(new Uri($uri), [
            'shop-id' => $this->shopIdProvider->getShopId(),
            'shop-url' => $this->shopUrl,
            'timestamp' => (string) (new \DateTime())->getTimestamp(),
            'sw-version' => $this->heyframeVersion,
            'app-version' => $app->getVersion(),
            'in-app-purchases' => \urlencode($this->inAppPurchase->getJWTByExtension($app->getName()) ?? ''),
            AuthMiddleware::HEYFRAME_CONTEXT_LANGUAGE => $context->getLanguageId(),
            AuthMiddleware::HEYFRAME_USER_LANGUAGE => $this->localeProvider->getLocaleFromContext($context),
        ]);

        return Uri::withQueryValue(
            $unsignedUri,
            'heyframe-shop-signature',
            (new RequestSigner())->signPayload($unsignedUri->getQuery(), $secret)
        );
    }
}
