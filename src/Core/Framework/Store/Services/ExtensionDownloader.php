<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use GuzzleHttp\Exception\ClientException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\PluginCollection;
use HeyFrame\Core\Framework\Plugin\PluginManagementService;
use HeyFrame\Core\Framework\Store\Exception\StoreApiException;
use HeyFrame\Core\Framework\Store\StoreException;
use HeyFrame\Core\Framework\Store\Struct\PluginDownloadDataStruct;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[Package('checkout')]
class ExtensionDownloader
{
    private readonly string $relativePluginDir;

    /**
     * @param EntityRepository<PluginCollection> $pluginRepository
     */
    public function __construct(
        private readonly EntityRepository $pluginRepository,
        private readonly StoreClient $storeClient,
        private readonly PluginManagementService $pluginManagementService,
        string $pluginDir,
        string $projectDir
    ) {
        $this->relativePluginDir = (new Filesystem())->makePathRelative($pluginDir, $projectDir);
    }

    public function download(string $technicalName, Context $context): PluginDownloadDataStruct
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('plugin.name', $technicalName));

        $plugin = $this->pluginRepository->search($criteria, $context)->getEntities()->first();

        if ($plugin !== null && $plugin->getManagedByComposer() && !str_starts_with($plugin->getPath() ?? '', $this->relativePluginDir)) {
            throw StoreException::cannotDeleteManaged($plugin->getName());
        }

        try {
            $data = $this->storeClient->getDownloadDataForPlugin($technicalName, $context);
        } catch (ClientException $e) {
            throw new StoreApiException($e);
        }

        $this->pluginManagementService->downloadStorePlugin($data, $context);

        return $data;
    }
}
