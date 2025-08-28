<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Cache\Event;

use HeyFrame\Core\Framework\Log\Package;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class HttpCacheHitEvent extends Event
{
    public function __construct(
        public readonly CacheItemInterface $item,
        public readonly Request $request,
        public readonly Response $response
    ) {
    }
}
