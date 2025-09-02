<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\Log\Monolog;

use HeyFrame\Core\Framework\Log\Monolog\ExcludeFlowEventHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ExcludeFlowEventHandler::class)]
class ExcludeFlowEventHandlerTest extends TestCase
{
    /**
     * @param list<string> $excludeList
     */
    #[DataProvider('cases')]
    public function testHandler(LogRecord $record, array $excludeList, bool $shouldBePassed): void
    {
        $innerHandler = $this->createMock(FingersCrossedHandler::class);
        $innerHandler->expects($shouldBePassed ? $this->once() : $this->never())->method('handle')->willReturn(true);

        $handler = new ExcludeFlowEventHandler(
            $innerHandler,
            $excludeList
        );

        $handler->handle($record);
    }

    /**
     * @return iterable<string, array{0: LogRecord, 1: list<string>, 2: bool}>
     */
    public static function cases(): iterable
    {
        // record, exclude list, should be passed
        yield 'event without exclude list' => [
            new LogRecord(new \DateTimeImmutable(), 'foo', Level::Alert, 'some message'),
            [],
            true,
        ];
    }
}
