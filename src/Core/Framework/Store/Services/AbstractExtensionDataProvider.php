<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Struct\ExtensionCollection;

/**
 * @internal
 */
#[Package('checkout')]
abstract class AbstractExtensionDataProvider
{
    abstract public function getInstalledExtensions(Context $context, bool $loadCloudExtensions = true, ?Criteria $searchCriteria = null): ExtensionCollection;

    abstract public function getAppEntityFromTechnicalName(string $technicalName, Context $context): AppEntity;

    abstract public function getAppEntityFromId(string $id, Context $context): AppEntity;

    abstract protected function getDecorated(): AbstractExtensionDataProvider;
}
