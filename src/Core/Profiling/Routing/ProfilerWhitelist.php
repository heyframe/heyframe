<?php declare(strict_types=1);

namespace HeyFrame\Core\Profiling\Routing;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\RouteScopeWhitelistInterface;
use HeyFrame\Core\Profiling\Controller\ProfilerController;

#[Package('framework')]
class ProfilerWhitelist implements RouteScopeWhitelistInterface
{
    public function applies(string $controllerClass): bool
    {
        return $controllerClass === ProfilerController::class;
    }
}
