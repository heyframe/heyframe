<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[Package('checkout')]
class CustomerValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if ($argument->getType() !== CustomerEntity::class) {
            return;
        }

        $loginRequired = $request->attributes->get(PlatformRequest::ATTRIBUTE_LOGIN_REQUIRED);

        if ($loginRequired !== true) {
            $route = $request->attributes->get('_route');

            throw CustomerException::missingRouteAnnotation('LoginRequired', $route);
        }

        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof ChannelContext) {
            $route = $request->attributes->get('_route');

            throw CustomerException::missingRouteChannel($route);
        }

        yield $context->getCustomer();
    }
}
