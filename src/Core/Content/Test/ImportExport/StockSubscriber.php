<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test\ImportExport;

use HeyFrame\Core\Content\ImportExport\Event\ImportExportBeforeExportRecordEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class StockSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ImportExportBeforeExportRecordEvent::class => 'onExport',
        ];
    }

    public function onExport(ImportExportBeforeExportRecordEvent $event): void
    {
        if ($event->getConfig()->get('sourceEntity') !== 'product') {
            return;
        }

        $keys = $event->getConfig()->getMapping()->getKeys();
        if (!\in_array('stock', $keys, true)) {
            return;
        }

        $record = $event->getRecord();
        ++$record['stock'];
        $event->setRecord($record);
    }
}
