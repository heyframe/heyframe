<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Product;

use HeyFrame\Core\Content\Product\Channel\ProductAvailableFilter;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\Adapter\Storage\AbstractKeyValueStorage;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\CustomField\CustomFieldService;
use HeyFrame\Elasticsearch\Framework\DataAbstractionLayer\CriteriaParser;
use OpenSearchDSL\BuilderInterface;
use OpenSearchDSL\Query\Compound\BoolQuery;
use OpenSearchDSL\Query\TermLevel\ExistsQuery;
use OpenSearchDSL\Query\TermLevel\RangeQuery;
use OpenSearchDSL\Query\TermLevel\TermQuery;

/**
 * @internal - This class is part of the internal API, optimized for read and should not be used directly.
 */
#[Package('inventory')]
class ProductCriteriaParser extends CriteriaParser
{
    public function __construct(
        EntityDefinitionQueryHelper $helper,
        CustomFieldService $customFieldService,
        private readonly AbstractKeyValueStorage $storage,
        private readonly CriteriaParser $decorated
    ) {
        parent::__construct($helper, $customFieldService, $storage);
    }

    public function parseFilter(Filter $filter, EntityDefinition $definition, string $root, Context $context): BuilderInterface
    {
        if (!$definition instanceof ProductDefinition) {
            return parent::parseFilter($filter, $definition, $root, $context);
        }

        if ($filter instanceof ProductAvailableFilter) {
            /**
             * @deprecated tag:v6.8.0 - this if statement will be always true
             */
            if (!Feature::isActive('v6.8.0.0') && !$this->storage->has(ElasticsearchOptimizeSwitch::FLAG)) {
                return $this->decorated->parseFilter($filter, $definition, $root, $context);
            }

            $query = new BoolQuery();

            $query->add(
                new TermQuery('active', true),
            );

            $query->add(
                new RangeQuery('visibility_' . $filter->getChannelId(), [RangeFilter::GTE => $filter->getVisibility()]),
            );

            return $query;
        }

        if ($filter instanceof EqualsFilter && \str_contains($filter->getField(), 'categoriesRo.id')) {
            /**
             * @deprecated tag:v6.8.0 - this if statement will be always true
             */
            if (!Feature::isActive('v6.8.0.0') && !$this->storage->has(ElasticsearchOptimizeSwitch::FLAG)) {
                return $this->decorated->parseFilter($filter, $definition, $root, $context);
            }

            if ($filter->getValue() === null) {
                return new BoolQuery([
                    BoolQuery::MUST_NOT => new ExistsQuery('categoryTree'),
                ]);
            }

            return new TermQuery('categoryTree', $filter->getValue());
        }

        return parent::parseFilter($filter, $definition, $root, $context);
    }
}
