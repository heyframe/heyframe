<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Subscriber;

use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Event\InstalledExtensionsListingLoadedEvent;
use HeyFrame\Core\Framework\Store\Struct\ExtensionStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class InstalledExtensionsListingLoadedSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     *
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(private readonly EntityRepository $appRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InstalledExtensionsListingLoadedEvent::class => 'removeAppsWithService',
        ];
    }

    /**
     * Remove apps from the listing which have an installed service equivalent
     */
    public function removeAppsWithService(InstalledExtensionsListingLoadedEvent $event): void
    {
        $existingServices = $this->appRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('selfManaged', true)),
            $event->context
        )->getEntities();

        $names = array_values($existingServices->map(fn (AppEntity $app) => $app->getName()));

        $event->extensionCollection = $event->extensionCollection->filter(
            fn (ExtensionStruct $ext) => !\in_array($ext->getName(), $names, true)
        );
    }
}
