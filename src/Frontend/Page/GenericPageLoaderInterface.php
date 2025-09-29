<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Page;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
interface GenericPageLoaderInterface
{
    public function load(Request $request, ChannelContext $context): Page;
}
