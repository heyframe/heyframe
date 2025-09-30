<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet\Footer;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
interface FooterPageletLoaderInterface
{
    public function load(Request $request, ChannelContext $channelContext): FooterPagelet;
}
