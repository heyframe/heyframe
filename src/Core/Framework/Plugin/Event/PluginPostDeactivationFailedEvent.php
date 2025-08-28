<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Context\ActivateContext;
use HeyFrame\Core\Framework\Plugin\PluginEntity;

#[Package('framework')]
class PluginPostDeactivationFailedEvent extends PluginLifecycleEvent
{
    public function __construct(
        PluginEntity $plugin,
        private readonly ActivateContext $context,
        private readonly ?\Throwable $exception = null
    ) {
        parent::__construct($plugin);
    }

    public function getContext(): ActivateContext
    {
        return $this->context;
    }

    public function getException(): ?\Throwable
    {
        return $this->exception;
    }
}
