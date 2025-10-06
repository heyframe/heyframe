<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Sitemap\Provider;

use HeyFrame\Core\Content\Sitemap\Provider\CustomUrlProvider;
use HeyFrame\Core\Content\Sitemap\Service\ConfigHandler;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('discovery')]
#[CoversClass(CustomUrlProvider::class)]
class CustomUrlProviderTest extends TestCase
{
    public function testGetUrlsReturnsNoUrls(): void
    {
        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $channelContext = $this->createMock(ChannelContext::class);

        static::assertSame([], $customUrlProvider->getUrls($channelContext, 100)->getUrls());
    }

    public function testGetUrlsReturnsAllUrlsForChannel(): void
    {
        $channelContext = $this->createMock(ChannelContext::class);

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => new \DateTimeImmutable(),
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'channelId' => 2,
                ], [
                    'url' => 'bar',
                    'lastMod' => new \DateTimeImmutable(),
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'channelId' => $channelContext->getChannelId(),
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        static::assertCount(1, $customUrlProvider->getUrls($channelContext, 100)->getUrls());
    }

    public function testGetUrlsReturnsAllUrlsForChannelIdNull(): void
    {
        $channelContext = $this->createMock(ChannelContext::class);

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => new \DateTimeImmutable(),
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'channelId' => 2,
                ], [
                    'url' => 'bar',
                    'lastMod' => new \DateTimeImmutable(),
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'channelId' => null,
                ], [
                    'url' => 'fooBar',
                    'lastMod' => new \DateTimeImmutable(),
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'channelId' => null,
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        $urls = $customUrlProvider->getUrls($channelContext, 100)->getUrls();

        [$firstUrl, $secondUrl] = $urls;
        static::assertCount(2, $urls);
        static::assertSame('bar', $firstUrl->getLoc());
        static::assertSame('fooBar', $secondUrl->getLoc());
    }

    public function testGetUrlsReturnsNoUrlsWrongChannelId(): void
    {
        $channelContext = $this->createMock(ChannelContext::class);

        $configHandlerStub = $this->createMock(ConfigHandler::class);
        $configHandlerStub->method('get')
            ->with(ConfigHandler::CUSTOM_URLS_KEY)
            ->willReturn([
                [
                    'url' => 'foo',
                    'lastMod' => new \DateTimeImmutable(),
                    'changeFreq' => 'weekly',
                    'priority' => 0.5,
                    'channelId' => 2,
                ],
            ]);

        $customUrlProvider = $this->getCustomUrlProvider($configHandlerStub);

        static::assertEmpty($customUrlProvider->getUrls($channelContext, 100)->getUrls());
    }

    private function getCustomUrlProvider(ConfigHandler $configHandlerStub): CustomUrlProvider
    {
        return new CustomUrlProvider($configHandlerStub);
    }
}
