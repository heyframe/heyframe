<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\System\SystemConfig;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Exception\InvalidUuidException;
use HeyFrame\Core\System\SystemConfig\Event\BeforeSystemConfigMultipleChangedEvent;
use HeyFrame\Core\System\SystemConfig\Event\SystemConfigChangedHook;
use HeyFrame\Core\System\SystemConfig\Event\SystemConfigMultipleChangedEvent;
use HeyFrame\Core\System\SystemConfig\Exception\InvalidDomainException;
use HeyFrame\Core\System\SystemConfig\Exception\InvalidKeyException;
use HeyFrame\Core\System\SystemConfig\Exception\InvalidSettingValueException;
use HeyFrame\Core\System\SystemConfig\Store\MemoizedSystemConfigStore;
use HeyFrame\Core\System\SystemConfig\SymfonySystemConfigService;
use HeyFrame\Core\System\SystemConfig\SystemConfigLoader;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Core\System\SystemConfig\Util\ConfigReader;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
class SystemConfigServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    private SystemConfigService $systemConfigService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->systemConfigService = new SystemConfigService(
            static::getContainer()->get(Connection::class),
            static::getContainer()->get(ConfigReader::class),
            static::getContainer()->get(SystemConfigLoader::class),
            static::getContainer()->get('event_dispatcher'),
            new SymfonySystemConfigService([]),
            static::getContainer()->get(CacheTagCollector::class),
        );
    }

    /**
     * @return list<array{mixed}>
     */
    public static function differentTypesProvider(): array
    {
        return [
            [true],
            [false],
            [null],
            [0],
            [1234],
            [1243.42314],
            [''],
            ['test'],
            [['foo' => 'bar']],
        ];
    }

    /**
     * @param float|bool|int|string|array<mixed>|null $expected
     */
    #[DataProvider('differentTypesProvider')]
    public function testSetGetDifferentTypes(array|float|bool|int|string|null $expected): void
    {
        $this->systemConfigService->set('foo.bar', $expected);
        $actual = $this->systemConfigService->get('foo.bar');
        static::assertSame($expected, $actual);
    }

    /**
     * @return list<array{mixed, string}>
     */
    public static function getStringProvider(): array
    {
        return [
            [true, '1'],
            [false, ''],
            [null, ''],
            [0, '0'],
            [1234, '1234'],
            [1243.42314, '1243.42314'],
            ['', ''],
            ['test', 'test'],
            [['foo' => 'bar'], ''],
        ];
    }

    /**
     * @param array<mixed>|bool|int|float|string|null $writtenValue
     */
    #[DataProvider('getStringProvider')]
    public function testGetString($writtenValue, string $expected): void
    {
        $this->systemConfigService->set('foo.bar', $writtenValue);
        if (\is_array($writtenValue)) {
            $this->expectException(InvalidSettingValueException::class);
            $this->expectExceptionMessage('Invalid value for \'foo.bar\'. Must be of type \'string\'. But is of type \'array\'');
        }
        $actual = $this->systemConfigService->getString('foo.bar');
        static::assertSame($expected, $actual);
    }

    /**
     * @return list<array{mixed, int}>
     */
    public static function getIntProvider(): array
    {
        return [
            [true, 1],
            [false, 0],
            [null, 0],
            [0, 0],
            [1234, 1234],
            [1243.42314, 1243],
            ['', 0],
            ['test', 0],
            [['foo' => 'bar'], 0],
        ];
    }

    /**
     * @param float|bool|int|string|array<mixed>|null $writtenValue
     */
    #[DataProvider('getIntProvider')]
    public function testGetInt(array|float|bool|int|string|null $writtenValue, int $expected): void
    {
        $this->systemConfigService->set('foo.bar', $writtenValue);
        if (\is_array($writtenValue)) {
            $this->expectException(InvalidSettingValueException::class);
            $this->expectExceptionMessage('Invalid value for \'foo.bar\'. Must be of type \'int\'. But is of type \'array\'');
        }
        $actual = $this->systemConfigService->getInt('foo.bar');
        static::assertSame($expected, $actual);
    }

    /**
     * @return list<array{mixed, float}>
     */
    public static function getFloatProvider(): array
    {
        return [
            [true, 1],
            [false, 0],
            [null, 0],
            [0, 0],
            [1234, 1234],
            [1243.42314, 1243.42314],
            ['', 0],
            ['test', 0],
            [['foo' => 'bar'], 0],
        ];
    }

    /**
     * @param float|bool|int|string|array<mixed>|null $writtenValue
     */
    #[DataProvider('getFloatProvider')]
    public function testGetFloat(array|float|bool|int|string|null $writtenValue, float $expected): void
    {
        $this->systemConfigService->set('foo.bar', $writtenValue);
        if (\is_array($writtenValue)) {
            $this->expectException(InvalidSettingValueException::class);
            $this->expectExceptionMessage('Invalid value for \'foo.bar\'. Must be of type \'float\'. But is of type \'array\'');
        }
        $actual = $this->systemConfigService->getFloat('foo.bar');
        static::assertSame($expected, $actual);
    }

    /**
     * @return list<array{mixed, bool}>
     */
    public static function getBoolProvider(): array
    {
        return [
            [true, true],
            [false, false],
            [null, false],
            [0, false],
            [1234, true],
            [1243.42314, true],
            ['', false],
            ['test', true],
            [['foo' => 'bar'], true],
            [[], false],
        ];
    }

    /**
     * @param float|bool|int|string|array<mixed>|null $writtenValue
     */
    #[DataProvider('getBoolProvider')]
    public function testGetBool(array|float|bool|int|string|null $writtenValue, bool $expected): void
    {
        $this->systemConfigService->set('foo.bar', $writtenValue);
        $actual = $this->systemConfigService->getBool('foo.bar');
        static::assertSame($expected, $actual);
    }

    /**
     * mysql 5.7.30 casts 0.0 to 0
     */
    public function testFloatZero(): void
    {
        $this->systemConfigService->set('foo.bar', 0.0);
        $actual = $this->systemConfigService->get('foo.bar');
        static::assertSame(0.0, $actual);
    }

    public function testSetGetChannel(): void
    {
        $this->systemConfigService->set('foo.bar', 'test');
        $actual = $this->systemConfigService->get('foo.bar', TestDefaults::CHANNEL);
        static::assertSame('test', $actual);

        $this->systemConfigService->set('foo.bar', 'override', TestDefaults::CHANNEL);
        $actual = $this->systemConfigService->get('foo.bar', TestDefaults::CHANNEL);
        static::assertSame('override', $actual);

        $this->systemConfigService->set('foo.bar', '', TestDefaults::CHANNEL);
        $actual = $this->systemConfigService->get('foo.bar', TestDefaults::CHANNEL);
        static::assertSame('', $actual);
    }

    public function testSetGetChannelBool(): void
    {
        $this->systemConfigService->set('foo.bar', false);
        $actual = $this->systemConfigService->get('foo.bar', TestDefaults::CHANNEL);
        static::assertFalse($actual);

        $this->systemConfigService->set('foo.bar', true, TestDefaults::CHANNEL);
        $actual = $this->systemConfigService->get('foo.bar', TestDefaults::CHANNEL);
        static::assertTrue($actual);
    }

    public function testGetDomainNoData(): void
    {
        $actual = $this->systemConfigService->getDomain('foo');
        static::assertSame([], $actual);

        $actual = $this->systemConfigService->getDomain('foo', null, true);
        static::assertSame([], $actual);

        $actual = $this->systemConfigService->getDomain('foo', TestDefaults::CHANNEL);
        static::assertSame([], $actual);

        $actual = $this->systemConfigService->getDomain('foo', TestDefaults::CHANNEL, true);
        static::assertSame([], $actual);
    }

    public function testGetDomain(): void
    {
        $this->systemConfigService->set('foo.a', 'a');
        $this->systemConfigService->set('foo.b', 'b');
        $this->systemConfigService->set('foo.c', 'c');
        $this->systemConfigService->set('foo.c', 'c override', TestDefaults::CHANNEL);

        $expected = [
            'foo.a' => 'a',
            'foo.b' => 'b',
            'foo.c' => 'c',
        ];
        $actual = $this->systemConfigService->getDomain('foo');
        static::assertSame($expected, $actual);

        $expected = [
            'foo.a' => 'a',
            'foo.b' => 'b',
            'foo.c' => 'c override',
        ];
        $actual = $this->systemConfigService->getDomain('foo', TestDefaults::CHANNEL, true);
        static::assertSame($expected, $actual);

        $expected = [
            'foo.c' => 'c override',
        ];
        $actual = $this->systemConfigService->getDomain('foo', TestDefaults::CHANNEL);
        static::assertSame($expected, $actual);
    }

    public function testGetDomainInherit(): void
    {
        $this->systemConfigService->set('foo.bar', 'test');
        $this->systemConfigService->set('foo.bar', 'override', TestDefaults::CHANNEL);
        $this->systemConfigService->set('foo.bar', '', TestDefaults::CHANNEL);

        $expected = ['foo.bar' => 'test'];
        $actual = $this->systemConfigService->getDomain('foo', TestDefaults::CHANNEL, true);

        static::assertSame($expected, $actual);
    }

    public function testGetDomainInheritWithBooleanValue(): void
    {
        $this->systemConfigService->set('foo.bar', true);
        $actual = $this->systemConfigService->getDomain('foo', TestDefaults::CHANNEL, true);

        // assert that the service reads the default value, when no channel-specific value is configured
        static::assertSame(['foo.bar' => true], $actual);

        $this->systemConfigService->set('foo.bar', false, TestDefaults::CHANNEL);
        $actual = $this->systemConfigService->getDomain('foo', TestDefaults::CHANNEL, true);

        // assert that the service reads the channel-specific value when one is configured
        static::assertSame(['foo.bar' => false], $actual);
    }

    public function testGetDomainWithDots(): void
    {
        $this->systemConfigService->set('foo.a', 'a');
        $actual = $this->systemConfigService->getDomain('foo.');
        static::assertSame(['foo.a' => 'a'], $actual);
    }

    public function testDelete(): void
    {
        $this->systemConfigService->set('foo', 'bar');
        $this->systemConfigService->set('foo', 'bar override', TestDefaults::CHANNEL);

        $this->systemConfigService->delete('foo');
        $actual = $this->systemConfigService->get('foo');
        static::assertNull($actual);
        $actual = $this->systemConfigService->get('foo', TestDefaults::CHANNEL);
        static::assertSame('bar override', $actual);

        $this->systemConfigService->delete('foo', TestDefaults::CHANNEL);
        $actual = $this->systemConfigService->get('foo', TestDefaults::CHANNEL);
        static::assertNull($actual);
    }

    public function testGetDomainEmptyThrows(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->systemConfigService->getDomain('');
    }

    public function testGetDomainOnlySpacesThrows(): void
    {
        $this->expectException(InvalidDomainException::class);
        $this->systemConfigService->getDomain('     ');
    }

    public function testSetEmptyKeyThrows(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->systemConfigService->set('', 'throws error');
    }

    public function testSetOnlySpacesKeyThrows(): void
    {
        $this->expectException(InvalidKeyException::class);
        $this->systemConfigService->set('          ', 'throws error');
    }

    public function testSetInvalidChannelThrows(): void
    {
        $this->expectException(InvalidUuidException::class);
        $this->systemConfigService->set('foo.bar', 'test', 'invalid uuid');
    }

    public function testWebhookEventsFired(): void
    {
        $eventDispatcher = static::getContainer()->get('event_dispatcher');

        $called = false;

        $this->addEventListener($eventDispatcher, SystemConfigChangedHook::class, function (SystemConfigChangedHook $event) use (&$called): void {
            static::assertSame([
                'changes' => ['foo.bar'],
                'channelId' => TestDefaults::CHANNEL,
            ], $event->getWebhookPayload());

            $called = true;
        });

        $this->systemConfigService->set('foo.bar', 'test', TestDefaults::CHANNEL);

        static::assertTrue($called);
    }

    public function testDeleteExtensionConfigurationDeletesAcrossAllChannels(): void
    {
        $extensionName = 'SwagTest';
        $configKey1 = $extensionName . '.config.testSetting1';
        $configKey2 = $extensionName . '.config.testSetting2';

        // Create three records, 2 global and 1 sales channel specific
        $this->systemConfigService->set($configKey1, 'global_value');
        $this->systemConfigService->set($configKey1, 'sales_channel_value', TestDefaults::CHANNEL);
        $this->systemConfigService->set($configKey2, true);

        // Verify that the records exist
        static::assertSame('global_value', $this->systemConfigService->get($configKey1));
        static::assertSame('sales_channel_value', $this->systemConfigService->get($configKey1, TestDefaults::CHANNEL));
        static::assertTrue($this->systemConfigService->getBool($configKey2));
        static::assertTrue($this->systemConfigService->getBool($configKey2, TestDefaults::CHANNEL));

        // Add event listeners to capture dispatched events, structured by scope
        $dispatchedEvents = [];
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');

        $listener = function (
            BeforeSystemConfigMultipleChangedEvent|SystemConfigMultipleChangedEvent|SystemConfigChangedHook $event
        ) use (&$dispatchedEvents): void {
            $eventClass = $event::class;

            if ($event instanceof SystemConfigChangedHook) {
                $payload = $event->getWebhookPayload();
                static::assertArrayHasKey('channelId', $payload);
                $channelId = $payload['channelId'];
            } else {
                $channelId = $event->getChannelId();
            }

            $scope = $channelId === null ? 'global' : 'sales_channel';
            $dispatchedEvents[$eventClass][$scope][] = $event;
        };

        $this->addEventListener($eventDispatcher, BeforeSystemConfigMultipleChangedEvent::class, $listener);
        $this->addEventListener($eventDispatcher, SystemConfigMultipleChangedEvent::class, $listener);
        $this->addEventListener($eventDispatcher, SystemConfigChangedHook::class, $listener);

        $this->systemConfigService->deleteExtensionConfiguration($extensionName, [
            ['elements' => [['name' => 'testSetting1'], ['name' => 'testSetting2']]],
        ]);

        // Reset the memoized values
        $this->getContainer()->get(MemoizedSystemConfigStore::class)->reset();

        // All records should be deleted
        static::assertNull($this->systemConfigService->get($configKey1));
        static::assertNull($this->systemConfigService->get($configKey1, TestDefaults::CHANNEL));
        static::assertFalse($this->systemConfigService->getBool($configKey2));
        static::assertFalse($this->systemConfigService->getBool($configKey2, TestDefaults::CHANNEL));

        // Assert that the events were dispatched correctly for the global scope
        static::assertCount(1, $dispatchedEvents[BeforeSystemConfigMultipleChangedEvent::class]['global']);
        static::assertCount(1, $dispatchedEvents[SystemConfigMultipleChangedEvent::class]['global']);
        static::assertCount(1, $dispatchedEvents[SystemConfigChangedHook::class]['global']);

        // Assert that the events were dispatched correctly for the sales channel scope
        static::assertCount(1, $dispatchedEvents[BeforeSystemConfigMultipleChangedEvent::class]['sales_channel']);
        static::assertCount(1, $dispatchedEvents[SystemConfigMultipleChangedEvent::class]['sales_channel']);
        static::assertCount(1, $dispatchedEvents[SystemConfigChangedHook::class]['sales_channel']);

        // Assert content of bulk events
        $globalMultipleEvent = $dispatchedEvents[SystemConfigMultipleChangedEvent::class]['global'][0];
        static::assertInstanceOf(SystemConfigMultipleChangedEvent::class, $globalMultipleEvent);
        static::assertEquals([$configKey1, $configKey2], array_keys($globalMultipleEvent->getConfig()));

        $channelMultipleEvent = $dispatchedEvents[SystemConfigMultipleChangedEvent::class]['sales_channel'][0];
        static::assertInstanceOf(SystemConfigMultipleChangedEvent::class, $channelMultipleEvent);
        static::assertEquals([$configKey1, $configKey2], array_keys($channelMultipleEvent->getConfig()));
    }
}
