<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Context\UninstallContext;
use HeyFrame\Core\Framework\Plugin\PluginEntity;

#[Package('framework')]
class PluginPreUninstallEvent extends PluginLifecycleEvent
{
    public function __construct(
        PluginEntity $plugin,
        private readonly UninstallContext $context
    ) {
        parent::__construct($plugin);
    }

    public function getContext(): UninstallContext
    {
        return $this->context;
    }
}
