<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\EventListener;

use HeyFrame\Core\ChannelRequest;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[Package('framework')]
class ResponseExceptionListener implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly bool $debug = false)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', -1],
            ],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getRequest()->attributes->get(ChannelRequest::ATTRIBUTE_IS_CHANNEL_REQUEST)) {
            return;
        }

        $exception = $event->getThrowable();

        $event->setResponse((new ErrorResponseFactory())->getResponseFromException($exception, $this->debug));
    }
}
