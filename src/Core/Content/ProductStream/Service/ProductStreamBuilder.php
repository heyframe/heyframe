<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\Service;

use HeyFrame\Core\Content\ProductStream\Exception\NoFilterException;
use HeyFrame\Core\Content\ProductStream\ProductStreamCollection;
use HeyFrame\Core\Content\ProductStream\ProductStreamEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\EntityNotFoundException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\SearchRequestException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Parser\QueryStringParser;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductStreamBuilder implements ProductStreamBuilderInterface
{
    /**
     * @internal
     *
     * @param EntityRepository<ProductStreamCollection> $repository
     */
    public function __construct(
        private readonly EntityRepository $repository,
        private readonly EntityDefinition $productDefinition
    ) {
    }

    public function buildFilters(string $id, Context $context): array
    {
        $criteria = new Criteria([$id]);

        /** @var ProductStreamEntity|null $stream */
        $stream = $this->repository
            ->search($criteria, $context)
            ->get($id);

        if (!$stream) {
            throw new EntityNotFoundException('product_stream', $id);
        }

        $data = $stream->getApiFilter();
        if (!$data) {
            throw new NoFilterException($id);
        }

        $filters = [];
        $exception = new SearchRequestException();

        foreach ($data as $filter) {
            $filters[] = QueryStringParser::fromArray($this->productDefinition, $filter, $exception, '');
        }

        return $filters;
    }
}
