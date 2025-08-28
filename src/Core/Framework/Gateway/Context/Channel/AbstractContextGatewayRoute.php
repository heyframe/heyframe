<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Channel;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ContextTokenResponse;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
abstract class AbstractContextGatewayRoute
{
    abstract public function getDecorated(): AbstractContextGatewayRoute;

    abstract public function load(Request $request, Cart $cart, ChannelContext $context): ContextTokenResponse;
}
