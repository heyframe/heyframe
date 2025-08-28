<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Context\DeactivateContext;
use HeyFrame\Core\Framework\Plugin\PluginEntity;

#[Package('framework')]
class PluginPostDeactivateEvent extends PluginLifecycleEvent
{
    public function __construct(
        PluginEntity $plugin,
        private readonly DeactivateContext $context
    ) {
        parent::__construct($plugin);
    }

    public function getContext(): DeactivateContext
    {
        return $this->context;
    }
}
