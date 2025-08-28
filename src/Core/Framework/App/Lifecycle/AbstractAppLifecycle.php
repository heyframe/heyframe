<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle;

use HeyFrame\Core\Framework\App\Lifecycle\Parameters\AppInstallParameters;
use HeyFrame\Core\Framework\App\Lifecycle\Parameters\AppUpdateParameters;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
abstract class AbstractAppLifecycle
{
    abstract public function getDecorated(): AbstractAppLifecycle;

    abstract public function install(Manifest $manifest, AppInstallParameters $parameters, Context $context): void;

    /**
     * @param array{id: string, roleId: string} $app
     */
    abstract public function update(Manifest $manifest, AppUpdateParameters $parameters, array $app, Context $context): void;

    /**
     * @param array{id: string} $app
     */
    abstract public function delete(string $appName, array $app, Context $context, bool $keepUserData = false): void;
}
