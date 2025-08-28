<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Controller;

use HeyFrame\Core\Framework\Api\ApiException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\RateLimiter\Exception\RateLimitExceededException;
use HeyFrame\Core\Framework\RateLimiter\RateLimiter;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use League\OAuth2\Server\AuthorizationServer;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('fundamentals@framework')]
class AuthController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AuthorizationServer $authorizationServer,
        private readonly PsrHttpFactory $psrHttpFactory,
        private readonly RateLimiter $rateLimiter,
    ) {
    }

    #[Route(path: '/api/oauth/token', name: 'api.oauth.token', defaults: ['auth_required' => false], methods: ['POST'])]
    public function token(Request $request): Response
    {
        $response = new Response();

        try {
            $cacheKey = $request->get('username') . '-' . $request->getClientIp();

            $this->rateLimiter->ensureAccepted(RateLimiter::OAUTH, $cacheKey);
        } catch (RateLimitExceededException $exception) {
            throw ApiException::notificationThrottled($exception->getWaitTime(), $exception);
        }

        $psr7Request = $this->psrHttpFactory->createRequest($request);
        $psr7Response = $this->psrHttpFactory->createResponse($response);

        $response = $this->authorizationServer->respondToAccessTokenRequest($psr7Request, $psr7Response);

        $this->rateLimiter->reset(RateLimiter::OAUTH, $cacheKey);

        return (new HttpFoundationFactory())->createResponse($response);
    }
}
