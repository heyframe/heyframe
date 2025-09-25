<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Plugin\PluginCollection;
use HeyFrame\Core\Framework\Store\Event\InstalledExtensionsListingLoadedEvent;
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
     * @param EntityRepository<PluginCollection> $pluginRepository
     */
    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
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

        $pluginCriteria = $searchCriteria ? clone $searchCriteria : new Criteria();
        $pluginCriteria->addAssociation('translations');

        $installedPlugins = $this->pluginRepository->search($pluginCriteria, $context)->getEntities();
        $pluginCollection = $this->extensionLoader->loadFromPluginCollection($context, $installedPlugins);

        $extensions = $pluginCollection;

        if ($loadCloudExtensions) {
            $extensions = $this->extensionListingLoader->load($extensions, $context);
        }

        $this->eventDispatcher->dispatch($event = new InstalledExtensionsListingLoadedEvent($extensions, $context));

        return $event->extensionCollection;
    }

    protected function getDecorated(): AbstractExtensionDataProvider
    {
        throw new DecorationPatternException(self::class);
    }
}
