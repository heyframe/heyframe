<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class SymfonyRouteScopeWhitelist implements RouteScopeWhitelistInterface
{
    /**
     * {@inheritdoc}
     */
    public function applies(string $controllerClass): bool
    {
        return str_starts_with($controllerClass, 'Symfony\\');
    }
}
