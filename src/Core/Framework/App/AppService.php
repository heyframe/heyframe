<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App;

use HeyFrame\Core\Framework\App\Lifecycle\AbstractAppLifecycle;
use HeyFrame\Core\Framework\App\Lifecycle\AppLifecycleIterator;
use HeyFrame\Core\Framework\App\Lifecycle\Parameters\AppInstallParameters;
use HeyFrame\Core\Framework\App\Lifecycle\RefreshableAppDryRun;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppService
{
    public function __construct(
        private readonly AppLifecycleIterator $appLifecycleIterator,
        private readonly AbstractAppLifecycle $appLifecycle
    ) {
    }

    /**
     * @param array<string> $installAppNames - Apps that should be installed
     *
     * @return list<array{manifest: Manifest, exception: \Exception}>
     */
    public function doRefreshApps(
        AppInstallParameters $parameters,
        Context $context,
        array $installAppNames = []
    ): array {
        return $this->appLifecycleIterator->iterateOverApps(
            $this->appLifecycle,
            $parameters,
            $context,
            $installAppNames
        );
    }

    public function getRefreshableAppInfo(Context $context): RefreshableAppDryRun
    {
        $appInfo = new RefreshableAppDryRun();

        $this->appLifecycleIterator->iterateOverApps(
            $appInfo,
            new AppInstallParameters(
                activate: false,
                acceptPermissions: false
            ),
            $context
        );

        return $appInfo;
    }
}
