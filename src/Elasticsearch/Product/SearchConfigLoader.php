<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Product;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader as CoreSearchConfigLoader;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
/**
 * @deprecated tag:v6.8.0 - will be removed, use HeyFrame\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader instead
 */
class SearchConfigLoader extends CoreSearchConfigLoader
{
}
