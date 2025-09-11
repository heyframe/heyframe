<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('discovery')]
class SitemapFilterOpenTagEvent extends Event implements HeyFrameEvent
{
    private string $openTag = '<?xml version="1.0" encoding="UTF-8"?><urlset %urlsetNamespaces%>';

    /**
     * @var array<string, string>
     */
    private array $urlsetNamespaces = [
        'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
    ];

    public function __construct(private readonly ChannelContext $channelContext)
    {
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getContext(): Context
    {
        return $this->channelContext->getContext();
    }

    public function getOpenTag(): string
    {
        return $this->openTag;
    }

    public function getFullOpenTag(): string
    {
        $namespaces = '';
        foreach ($this->urlsetNamespaces as $name => $namespace) {
            $namespaces .= \sprintf(' %s="%s"', $name, $namespace);
        }

        return strtr($this->openTag, [
            '%urlsetNamespaces%' => trim($namespaces),
        ]);
    }

    public function setOpenTag(string $openTag): void
    {
        $this->openTag = $openTag;
    }

    /**
     * @return array<string, string>
     */
    public function getUrlsetNamespaces(): array
    {
        return $this->urlsetNamespaces;
    }

    /**
     * @param array<string, string> $urlsetNamespaces
     */
    public function setUrlsetNamespaces(array $urlsetNamespaces): void
    {
        $this->urlsetNamespaces = $urlsetNamespaces;
    }

    public function addUrlsetNamespace(string $name, string $namespace): void
    {
        $this->urlsetNamespaces[$name] = $namespace;
    }
}
