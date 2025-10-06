<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('discovery')]
class SitemapGeneratedEvent extends Event implements HeyFrameEvent
{
    public function __construct(private readonly ChannelContext $context)
    {
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }
}
