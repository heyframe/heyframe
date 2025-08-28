<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\TestCaseBase;

use HeyFrame\Core\Framework\Event\EventData\MailRecipientStruct;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Event\MailAware;
use HeyFrame\Core\System\Channel\ChannelContext;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

trait MailTemplateTestBehaviour
{
    use EventDispatcherBehaviour;

    /**
     * @param class-string<object> $expectedClass
     */
    public static function assertMailEvent(
        string $expectedClass,
        HeyFrameEvent $event,
        ChannelContext $channelContext
    ): void {
        TestCase::assertInstanceOf($expectedClass, $event);
        TestCase::assertSame($channelContext->getContext(), $event->getContext());
    }

    public static function assertMailRecipientStructEvent(MailRecipientStruct $expectedStruct, MailAware $event): void
    {
        TestCase::assertSame($expectedStruct->getRecipients(), $event->getMailStruct()->getRecipients());
    }

    /**
     * @template TEvent of Event
     *
     * @param class-string<TEvent> $eventName
     * @param TEvent|null $eventResult
     */
    protected function catchEvent(string $eventName, ?object &$eventResult): void
    {
        $eventDispatcher = static::getContainer()->get('event_dispatcher');
        $this->addEventListener($eventDispatcher, $eventName, static function ($event) use (&$eventResult): void {
            $eventResult = $event;
        });
    }
}
