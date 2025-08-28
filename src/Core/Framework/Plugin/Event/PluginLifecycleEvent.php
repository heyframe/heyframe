<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Event;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\PluginEntity;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
abstract class PluginLifecycleEvent extends Event
{
    public function __construct(private readonly PluginEntity $plugin)
    {
    }

    public function getPlugin(): PluginEntity
    {
        return $this->plugin;
    }
}
