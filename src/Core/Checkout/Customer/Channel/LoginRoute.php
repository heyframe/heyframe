<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Service\EmailIdnConverter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\RateLimiter\Exception\RateLimitExceededException;
use HeyFrame\Core\Framework\RateLimiter\RateLimiter;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ContextTokenResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class LoginRoute extends AbstractLoginRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AccountService $accountService,
        private readonly RequestStack $requestStack,
        private readonly RateLimiter $rateLimiter
    ) {
    }

    public function getDecorated(): AbstractLoginRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/account/login', name: 'front-api.account.login', methods: ['POST'])]
    public function login(#[\SensitiveParameter] RequestDataBag $data, ChannelContext $context): ContextTokenResponse
    {
        EmailIdnConverter::encodeDataBag($data);
        $email = (string) $data->get('email', $data->get('username'));

        if ($this->requestStack->getMainRequest() !== null) {
            $cacheKey = strtolower($email) . '-' . $this->requestStack->getMainRequest()->getClientIp();

            try {
                $this->rateLimiter->ensureAccepted(RateLimiter::LOGIN_ROUTE, $cacheKey);
            } catch (RateLimitExceededException $exception) {
                throw CustomerException::customerAuthThrottledException($exception->getWaitTime(), $exception);
            }
        }

        $token = $this->accountService->loginByCredentials(
            $email,
            (string) $data->get('password'),
            $context
        );

        if (isset($cacheKey)) {
            $this->rateLimiter->reset(RateLimiter::LOGIN_ROUTE, $cacheKey);
        }

        return new ContextTokenResponse($token);
    }
}
