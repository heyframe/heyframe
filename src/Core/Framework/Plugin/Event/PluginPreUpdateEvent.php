<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Context\UpdateContext;
use HeyFrame\Core\Framework\Plugin\PluginEntity;

#[Package('framework')]
class PluginPreUpdateEvent extends PluginLifecycleEvent
{
    public function __construct(
        PluginEntity $plugin,
        private readonly UpdateContext $context
    ) {
        parent::__construct($plugin);
    }

    public function getContext(): UpdateContext
    {
        return $this->context;
    }
}
