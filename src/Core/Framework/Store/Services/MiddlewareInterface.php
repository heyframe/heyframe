<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\Log\Package;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
#[Package('checkout')]
interface MiddlewareInterface
{
    /**
     * Will be called after the request.
     *
     * @param ResponseInterface $response - Response we got.
     * @param RequestInterface $request - Request that was sent.
     */
    public function __invoke(ResponseInterface $response, RequestInterface $request): ResponseInterface;
}
