<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Sitemap\Channel;

use HeyFrame\Core\Content\Sitemap\Channel\SitemapFileRoute;
use HeyFrame\Core\Framework\Extensions\ExtensionDispatcher;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Tests\Examples\GetSitemapFileExample;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[CoversClass(SitemapFileRoute::class)]
class SitemapFileRouteTest extends TestCase
{
    public function testExtension(): void
    {
        $fileSystem = $this->createMock(FilesystemOperator::class);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new GetSitemapFileExample());

        $extensionDispatcher = new ExtensionDispatcher($dispatcher);

        $route = new SitemapFileRoute($fileSystem, $extensionDispatcher);

        $request = new Request();
        $context = $this->createMock(ChannelContext::class);
        $filePath = 'test.xml.gz';

        $response = $route->getSitemapFile($request, $context, $filePath);

        static::assertSame('Hello World!', $response->getContent());
    }
}
