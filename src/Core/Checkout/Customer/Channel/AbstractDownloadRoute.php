<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
abstract class AbstractDownloadRoute
{
    abstract public function getDecorated(): AbstractDownloadRoute;

    abstract public function load(Request $request, ChannelContext $context): Response;
}
