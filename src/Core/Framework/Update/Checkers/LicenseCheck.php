<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Update\Checkers;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Services\StoreClient;
use HeyFrame\Core\Framework\Update\Struct\ValidationResult;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;

#[Package('framework')]
class LicenseCheck
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly StoreClient $storeClient
    ) {
    }

    public function check(): ValidationResult
    {
        $licenseHost = $this->systemConfigService->get('core.store.licenseHost');

        if (empty($licenseHost) || $this->storeClient->isShopUpgradeable()) {
            return new ValidationResult('validHeyFrameLicense', true, 'validHeyFrameLicense');
        }

        return new ValidationResult('invalidHeyFrameLicense', false, 'invalidHeyFrameLicense');
    }
}
