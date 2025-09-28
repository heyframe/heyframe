<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Controller\Exception;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

#[Package('framework')]
class FrontendRouteNotFoundException extends RouteNotFoundException
{
    public function __construct(string $route, ?\Throwable $previous = null)
    {
        parent::__construct(
            \sprintf('Route "%s" not found.', $route),
            previous: $previous
        );
    }
}
