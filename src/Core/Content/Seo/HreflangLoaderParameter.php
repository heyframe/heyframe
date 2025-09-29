<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('inventory')]
class HreflangLoaderParameter
{
    /**
     * @param array<string, mixed> $routeParameters
     */
    public function __construct(
        protected string $route,
        protected array $routeParameters,
        protected ChannelContext $channelContext,
    ) {
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
