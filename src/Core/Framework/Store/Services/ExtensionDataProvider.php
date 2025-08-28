<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Plugin\PluginCollection;
use HeyFrame\Core\Framework\Store\Event\InstalledExtensionsListingLoadedEvent;
use HeyFrame\Core\Framework\Store\StoreException;
use HeyFrame\Core\Framework\Store\Struct\ExtensionCollection;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('checkout')]
class ExtensionDataProvider extends AbstractExtensionDataProvider
{
    final public const HEADER_NAME_TOTAL_COUNT = 'SW-Meta-Total';

    /**
     * @param EntityRepository<AppCollection> $appRepository
     * @param EntityRepository<PluginCollection> $pluginRepository
     */
    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
        private readonly EntityRepository $appRepository,
        private readonly EntityRepository $pluginRepository,
        private readonly ExtensionListingLoader $extensionListingLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getInstalledExtensions(Context $context, bool $loadCloudExtensions = true, ?Criteria $searchCriteria = null): ExtensionCollection
    {
        $appCriteria = $searchCriteria ? clone $searchCriteria : new Criteria();
        $appCriteria->addAssociation('translations');
        $appCriteria->addFilter(new EqualsFilter('selfManaged', false));

        $installedApps = $this->appRepository->search($appCriteria, $context)->getEntities();

        $pluginCriteria = $searchCriteria ? clone $searchCriteria : new Criteria();
        $pluginCriteria->addAssociation('translations');

        $installedPlugins = $this->pluginRepository->search($pluginCriteria, $context)->getEntities();
        $pluginCollection = $this->extensionLoader->loadFromPluginCollection($context, $installedPlugins);

        $extensions = $this->extensionLoader->loadFromAppCollection($context, $installedApps)->merge($pluginCollection);

        if ($loadCloudExtensions) {
            $extensions = $this->extensionListingLoader->load($extensions, $context);
        }

        $this->eventDispatcher->dispatch($event = new InstalledExtensionsListingLoadedEvent($extensions, $context));

        return $event->extensionCollection;
    }

    public function getAppEntityFromTechnicalName(string $technicalName, Context $context): AppEntity
    {
        $criteria = (new Criteria())->addFilter(new EqualsFilter('name', $technicalName));
        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();

        if (!$app) {
            throw StoreException::extensionNotFoundFromTechnicalName($technicalName);
        }

        return $app;
    }

    public function getAppEntityFromId(string $id, Context $context): AppEntity
    {
        $criteria = new Criteria([$id]);
        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();

        if (!$app) {
            throw StoreException::extensionNotFoundFromId($id);
        }

        return $app;
    }

    protected function getDecorated(): AbstractExtensionDataProvider
    {
        throw new DecorationPatternException(self::class);
    }
}
