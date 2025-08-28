<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig;

use HeyFrame\Core\Framework\App\Template\TemplateCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\TermsAggregation;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @implements \IteratorAggregate<int, string>
 */
#[Package('framework')]
class AppTemplateIterator implements \IteratorAggregate
{
    /**
     * @internal
     *
     * @param EntityRepository<TemplateCollection> $templateRepository
     */
    public function __construct(
        private readonly \IteratorAggregate $templateIterator,
        private readonly EntityRepository $templateRepository
    ) {
    }

    public function getIterator(): \Traversable
    {
        yield from $this->templateIterator;

        yield from $this->getDatabaseTemplatePaths();
    }

    /**
     * @return array<string>
     */
    private function getDatabaseTemplatePaths(): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addAggregation(
            new TermsAggregation('path-names', 'path')
        );

        /** @var TermsResult $pathNames */
        $pathNames = $this->templateRepository->aggregate(
            $criteria,
            Context::createDefaultContext()
        )->get('path-names');

        return $pathNames->getKeys();
    }
}
