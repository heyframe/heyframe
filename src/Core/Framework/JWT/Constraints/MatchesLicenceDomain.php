<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\JWT\Constraints;

use HeyFrame\Core\Framework\JWT\JWTException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\InAppPurchase\Services\DecodedPurchasesCollectionStruct;
use HeyFrame\Core\Framework\Store\Services\StoreService;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint;

#[Package('checkout')]
final readonly class MatchesLicenceDomain implements Constraint
{
    public function __construct(
        private SystemConfigService $systemConfigService
    ) {
    }

    public function assert(Token $token): void
    {
        $domain = $this->systemConfigService->get(StoreService::CONFIG_KEY_STORE_LICENSE_DOMAIN);

        if (!$domain) {
            throw JWTException::missingDomain();
        }

        if (!$token instanceof UnencryptedToken) {
            throw JWTException::invalidJwt('Incorrect token type');
        }

        $purchases = DecodedPurchasesCollectionStruct::fromArray($token->claims()->all());

        $firstPurchase = $purchases->first();
        if (!$firstPurchase) {
            throw JWTException::invalidJwt('No purchases found in JWT');
        }

        if ($firstPurchase->sub !== $domain) {
            throw JWTException::invalidDomain($firstPurchase->sub);
        }
    }
}
