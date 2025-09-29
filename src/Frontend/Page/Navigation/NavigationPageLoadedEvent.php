<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Page\Navigation;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Frontend\Page\PageLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class NavigationPageLoadedEvent extends PageLoadedEvent
{
    public function __construct(
        protected NavigationPage $page,
        ChannelContext $channelContext,
        Request $request
    ) {
        parent::__construct($channelContext, $request);
    }

    public function getPage(): NavigationPage
    {
        return $this->page;
    }
}
