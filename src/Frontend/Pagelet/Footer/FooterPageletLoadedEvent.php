<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet\Footer;


use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Frontend\Pagelet\PageletLoadedEvent;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class FooterPageletLoadedEvent extends PageletLoadedEvent
{
    public function __construct(
        protected FooterPagelet $pagelet,
        ChannelContext $channelContext,
        Request $request
    ) {
        parent::__construct($channelContext, $request);
    }

    public function getPagelet(): FooterPagelet
    {
        return $this->pagelet;
    }
}
