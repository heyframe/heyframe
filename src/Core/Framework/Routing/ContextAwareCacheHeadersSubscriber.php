<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * @internal
 */
#[Package('framework')]
readonly class ContextAwareCacheHeadersSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ContextAwareCacheHeadersService $contextAwareCacheService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'store-api.scope.response' => ['onResponse', -1000],
        ];
    }

    public function onResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof ChannelContext) {
            return;
        }

        // Add context headers to the response
        $this->contextAwareCacheService->addContextHeaders($request, $response, $context);
    }
}
