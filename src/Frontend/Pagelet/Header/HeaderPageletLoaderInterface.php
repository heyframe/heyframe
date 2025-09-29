<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet\Header;


use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
interface HeaderPageletLoaderInterface
{
    public function load(Request $request, ChannelContext $context): HeaderPagelet;
}
