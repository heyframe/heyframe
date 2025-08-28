<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\RoutingException;

#[Package('checkout')]
class CustomerNotLoggedInRoutingException extends RoutingException
{
}
