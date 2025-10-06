<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Sitemap\Service;

use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Content\Sitemap\Provider\AbstractUrlProvider;
use HeyFrame\Core\Content\Sitemap\Provider\CustomUrlProvider;
use HeyFrame\Core\Content\Sitemap\Service\SitemapExporter;
use HeyFrame\Core\Content\Sitemap\Service\SitemapHandleFactoryInterface;
use HeyFrame\Core\Content\Sitemap\Service\SitemapHandleInterface;
use HeyFrame\Core\Content\Sitemap\SitemapException;
use HeyFrame\Core\Content\Sitemap\Struct\Url;
use HeyFrame\Core\Content\Sitemap\Struct\UrlResult;
use HeyFrame\Core\Framework\Api\Context\SystemSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainCollection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainEntity;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\Test\Generator;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 */
#[CoversClass(SitemapExporter::class)]
class SitemapExporterTest extends TestCase
{
    public function testGenerate(): void
    {
        $urlItems = [
            [
                'url' => '',
            ],
            [
                'url' => 'test/',
            ],
            [
                'url' => 'test',
            ],
        ];

        $urls = [];
        foreach ($urlItems as $item) {
            $url = new Url();
            $url->setLoc($item['url']);

            $urls[] = $url;
        }

        $urlResult = new UrlResult($urls, null);

        $customerUrlProvider = $this->createMock(CustomUrlProvider::class);
        $customerUrlProvider->expects($this->once())->method('getUrls')->willReturn($urlResult);

        $sitemapHandler1 = $this->createMock(SitemapHandleInterface::class);
        $sitemapHandler2 = $this->createMock(SitemapHandleInterface::class);
        $sitemapHandlerFactory = $this->createMock(SitemapHandleFactoryInterface::class);
        $sitemapHandlerFactory->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                $sitemapHandler1,
                $sitemapHandler2
            );

        $cacheItemPoolInterface = $this->createMock(CacheItemPoolInterface::class);
        $cacheItemPoolInterface->method('getItem')->willReturn(new CacheItem());

        $exporter = $this->createSitemapExporter($cacheItemPoolInterface, [$customerUrlProvider], $sitemapHandlerFactory);

        $languageId = Uuid::randomHex();
        $channel = $this->createChannel('testChannel', $languageId);

        $domainA = $this->createChannelDomain('testDomainA', 'https://test.com/', $languageId);
        $domainB = $this->createChannelDomain('testDomainB', 'https://test.com', $languageId);

        $channel->setDomains(new ChannelDomainCollection([$domainA, $domainB]));

        $channelContext = $this->createChannelContext($channel, []);

        $expectedUrls = [];
        foreach ($urls as $url) {
            $expectedUrl = clone $url;
            $expectedUrl->setLoc('https://test.com/' . $url->getLoc());
            $expectedUrls[] = $expectedUrl;
        }

        $sitemapHandler1->expects($this->once())->method('write')->with($expectedUrls);
        $sitemapHandler2->expects($this->once())->method('write')->with($expectedUrls);
        $exporter->generate($channelContext);
    }

    public function testDoesNotRefreshChannelWithRules(): void
    {
        $channel = $this->createChannel('channelWithRules');
        $rules = array_map(fn () => Uuid::randomHex(), range(0, 2));

        $domain = $this->createChannelDomain('testDomain', 'https://test.com', $channel->getLanguageId());
        $channel->setDomains(new ChannelDomainCollection([$domain]));

        $channelContext = $this->createChannelContext($channel, $rules);

        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn(new CacheItem());

        $cartRuleLoader = $this->createMock(CartRuleLoader::class);
        $exporter = $this->createSitemapExporter(cache: $cache, cartRuleLoader: $cartRuleLoader);
        $exporter->generate($channelContext);

        $cartRuleLoader->expects($this->never())->method('loadByToken');
    }

    public function testGenerateThrowsExceptionINoSitemapHandlesCreated(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn(new CacheItemMock());

        $exporter = $this->createSitemapExporter($cache);

        $channel = $this->createChannel('testChannel');
        $channelContext = $this->createChannelContext($channel, []);

        $this->expectException(SitemapException::class);
        $this->expectExceptionMessage('Invalid domain');
        $exporter->generate($channelContext, true);
    }

    public function testGenerateThrowsExceptionIfSitemapIsAlreadyLocked(): void
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cache->method('getItem')->willReturn(new CacheItemMock());

        $exporter = $this->createSitemapExporter($cache);

        $channel = $this->createChannel('testChannel');
        $channelContext = $this->createChannelContext($channel, []);

        $this->expectException(SitemapException::class);
        $this->expectExceptionMessage('Cannot acquire lock for sales channel testChannel and language ' . $channel->getLanguageId());
        $exporter->generate($channelContext);
    }

    /**
     * @param iterable<AbstractUrlProvider>|null $urlProvider
     */
    private function createSitemapExporter(
        CacheItemPoolInterface&MockObject $cache,
        ?iterable $urlProvider = null,
        (SitemapHandleFactoryInterface&MockObject)|null $sitemapHandleFactory = null,
        ?CartRuleLoader $cartRuleLoader = null
    ): SitemapExporter {
        return new SitemapExporter(
            $urlProvider ?? [],
            $cache,
            10,
            $this->createMock(FilesystemOperator::class),
            $sitemapHandleFactory ?? $this->createMock(SitemapHandleFactoryInterface::class),
            $this->createMock(EventDispatcher::class),
            $cartRuleLoader ?? $this->createMock(CartRuleLoader::class)
        );
    }

    private function createChannel(
        string $channelId,
        ?string $languageId = null
    ): ChannelEntity {
        $channel = new ChannelEntity();
        $channel->setId($channelId);
        $channel->setLanguageId($languageId ?? Uuid::randomHex());

        return $channel;
    }

    private function createChannelDomain(
        string $domainId,
        string $domainUrl,
        ?string $languageId = null
    ): ChannelDomainEntity {
        $channelDomain = new ChannelDomainEntity();
        $channelDomain->setId($domainId);
        $channelDomain->setUrl($domainUrl);
        $channelDomain->setLanguageId($languageId ?? Uuid::randomHex());

        return $channelDomain;
    }

    /**
     * @param array<string> $ruleIds
     */
    private function createChannelContext(ChannelEntity $channel, array $ruleIds): ChannelContext
    {
        $context = new Context(
            source: new SystemSource(),
            ruleIds: $ruleIds,
            languageIdChain: [$channel->getLanguageId()],
        );

        return Generator::generateChannelContext(
            baseContext: $context,
            channel: $channel,
        );
    }
}

/**
 * @internal
 */
class CacheItemMock implements CacheItemInterface
{
    public function getKey(): string
    {
        return Uuid::randomHex();
    }

    public function get(): mixed
    {
        return null;
    }

    public function isHit(): bool
    {
        return true;
    }

    public function set(mixed $value): static
    {
        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        return $this;
    }

    public function expiresAfter(\DateInterval|int|null $time): static
    {
        return $this;
    }
}
