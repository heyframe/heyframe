<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Indexing;

use HeyFrame\Core\Framework\App\Event\AppActivatedEvent;
use HeyFrame\Core\Framework\App\Event\AppDeactivatedEvent;
use HeyFrame\Core\Framework\App\Event\AppDeletedEvent;
use HeyFrame\Core\Framework\App\Event\AppInstalledEvent;
use HeyFrame\Core\Framework\App\Event\AppUpdatedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\MessageQueue\IterateEntityIndexerMessage;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostDeactivateEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostInstallEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostUninstallEvent;
use HeyFrame\Core\Framework\Plugin\Event\PluginPostUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[Package('after-sales')]
class FlowIndexerSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginPostInstallEvent::class => 'refreshPlugin',
            PluginPostActivateEvent::class => 'refreshPlugin',
            PluginPostUpdateEvent::class => 'refreshPlugin',
            PluginPostDeactivateEvent::class => 'refreshPlugin',
            PluginPostUninstallEvent::class => 'refreshPlugin',
            AppInstalledEvent::class => 'refreshPlugin',
            AppUpdatedEvent::class => 'refreshPlugin',
            AppActivatedEvent::class => 'refreshPlugin',
            AppDeletedEvent::class => 'refreshPlugin',
            AppDeactivatedEvent::class => 'refreshPlugin',
        ];
    }

    public function refreshPlugin(): void
    {
        // Schedule indexer to update flows
        $this->messageBus->dispatch(new IterateEntityIndexerMessage(FlowIndexer::NAME, null));
    }
}
