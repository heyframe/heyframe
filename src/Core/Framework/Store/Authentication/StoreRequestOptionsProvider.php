<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Authentication;

use HeyFrame\Core\Framework\Api\Context\Exception\InvalidContextSourceException;
use HeyFrame\Core\Framework\Api\Context\SystemSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Services\InstanceService;
use HeyFrame\Core\Framework\Store\StoreException;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Core\System\User\UserCollection;

/**
 * @internal
 */
#[Package('checkout')]
class StoreRequestOptionsProvider extends AbstractStoreRequestOptionsProvider
{
    final public const CONFIG_KEY_STORE_LICENSE_DOMAIN = 'core.store.licenseHost';
    final public const CONFIG_KEY_STORE_SHOP_SECRET = 'core.store.shopSecret';

    final public const HEYFRAME_PLATFORM_TOKEN_HEADER = 'X-HeyFrame-Platform-Token';
    final public const HEYFRAME_SHOP_SECRET_HEADER = 'X-HeyFrame-Shop-Secret';

    /**
     * @param EntityRepository<UserCollection> $userRepository
     */
    public function __construct(
        private readonly EntityRepository $userRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly InstanceService $instanceService,
        private readonly LocaleProvider $localeProvider,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getAuthenticationHeader(Context $context): array
    {
        return array_filter([
            self::HEYFRAME_PLATFORM_TOKEN_HEADER => $this->getUserStoreToken($context),
            self::HEYFRAME_SHOP_SECRET_HEADER => $this->systemConfigService->getString(self::CONFIG_KEY_STORE_SHOP_SECRET),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function getDefaultQueryParameters(Context $context): array
    {
        return [
            'heyframeVersion' => $this->instanceService->getHeyFrameVersion(),
            'language' => $this->localeProvider->getLocaleFromContext($context),
            'domain' => $this->getLicenseDomain(),
        ];
    }

    private function getUserStoreToken(Context $context): ?string
    {
        try {
            return $this->getTokenFromAdmin($context);
        } catch (InvalidContextSourceException) {
            return $this->getTokenFromSystem($context);
        }
    }

    private function getTokenFromAdmin(Context $context): ?string
    {
        $contextSource = $this->ensureAdminApiSource($context);
        $userId = $contextSource->getUserId();
        if ($userId === null) {
            throw StoreException::invalidContextSourceUser($contextSource::class);
        }

        return $this->fetchUserStoreToken(new Criteria([$userId]), $context);
    }

    private function getTokenFromSystem(Context $context): ?string
    {
        $contextSource = $context->getSource();
        if (!($contextSource instanceof SystemSource)) {
            throw StoreException::invalidContextSource(SystemSource::class, $contextSource::class);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new NotEqualsFilter('storeToken', null));

        return $this->fetchUserStoreToken($criteria, $context);
    }

    private function fetchUserStoreToken(Criteria $criteria, Context $context): ?string
    {
        return $this->userRepository->search($criteria, $context)->first()?->getStoreToken();
    }

    private function getLicenseDomain(): string
    {
        return $this->systemConfigService->getString(self::CONFIG_KEY_STORE_LICENSE_DOMAIN);
    }
}
