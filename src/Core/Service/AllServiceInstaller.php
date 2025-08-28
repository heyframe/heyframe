<?php declare(strict_types=1);

namespace HeyFrame\Core\Service;

use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\Event\NewServicesInstalledEvent;
use HeyFrame\Core\Service\Message\InstallServicesMessage;
use HeyFrame\Core\Service\ServiceRegistry\Client;
use HeyFrame\Core\Service\ServiceRegistry\ServiceEntry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[Package('framework')]
class AllServiceInstaller
{
    /**
     * @internal
     *
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly Client $serviceRegistryClient,
        private readonly ServiceLifecycle $serviceLifecycle,
        private readonly EntityRepository $appRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * This is a low-level class that is responsible for installing all services.
     * It should only be called from a higher-level with 'state' awareness class, Specifically: HeyFrame\Core\Service\LifecycleManager
     *
     * @return array<string> The newly installed services
     */
    public function install(Context $context): array
    {
        $existingServices = $this->appRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('selfManaged', true)),
            $context
        );

        $installedServices = [];
        $newServices = $this->getNewServices($existingServices);
        foreach ($newServices as $service) {
            $result = $this->serviceLifecycle->install($service, $context);

            if ($result) {
                $installedServices[] = $service->name;
            }
        }

        if (!empty($installedServices)) {
            $this->eventDispatcher->dispatch(new NewServicesInstalledEvent());
        }

        return $installedServices;
    }

    public function scheduleInstall(): void
    {
        $this->messageBus->dispatch(new InstallServicesMessage());
    }

    /**
     * @param EntitySearchResult<AppCollection> $installedServices
     *
     * @return array<ServiceEntry>
     */
    private function getNewServices(EntitySearchResult $installedServices): array
    {
        $names = $installedServices->map(fn (AppEntity $app) => $app->getName());

        return array_filter(
            $this->serviceRegistryClient->getAll(),
            static fn (ServiceEntry $service) => !\in_array($service->name, $names, true)
        );
    }
}
