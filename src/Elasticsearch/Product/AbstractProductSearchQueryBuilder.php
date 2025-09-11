<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Product;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use OpenSearchDSL\BuilderInterface;

#[Package('framework')]
abstract class AbstractProductSearchQueryBuilder
{
    abstract public function getDecorated(): AbstractProductSearchQueryBuilder;

    abstract public function build(Criteria $criteria, Context $context): BuilderInterface;
}
