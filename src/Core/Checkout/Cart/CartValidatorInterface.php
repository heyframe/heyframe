<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('checkout')]
interface CartValidatorInterface
{
    public function validate(Cart $cart, ErrorCollection $errors, ChannelContext $context): void;
}
