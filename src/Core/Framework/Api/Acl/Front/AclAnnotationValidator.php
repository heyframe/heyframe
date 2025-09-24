<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Acl\Front;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Api\ApiException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\KernelListenerPriorities;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[Package('framework')]
class AclAnnotationValidator implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['validate', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_SCOPE_VALIDATE],
            ],
        ];
    }

    public function validate(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        $privileges = $request->attributes->get(PlatformRequest::ATTRIBUTE_FRONT_ACL);
        if (!$privileges) {
            return;
        }
        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof ChannelContext) {
            throw ApiException::missingPrivileges([]);
        }
        foreach ($privileges as $privilege) {
            if (!$context->isAllowed($privilege)) {
                throw ApiException::missingPrivileges([$privilege]);
            }
        }
    }
}
