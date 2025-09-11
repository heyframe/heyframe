<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NandFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Elasticsearch\Framework\Indexing\Event\ElasticsearchIndexerLanguageCriteriaEvent;
use Psr\EventDispatcher\EventDispatcherInterface;

#[Package('framework')]
class ElasticsearchLanguageProvider
{
    /**
     * @internal
     *
     * @param EntityRepository<LanguageCollection> $languageRepository
     */
    public function __construct(
        private readonly EntityRepository $languageRepository,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getLanguages(Context $context): LanguageCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new NandFilter([new EqualsFilter('channels.id', null)]));
        $criteria->addSorting(new FieldSorting('id'));

        $this->eventDispatcher->dispatch(new ElasticsearchIndexerLanguageCriteriaEvent($criteria, $context));

        return $this->languageRepository->search($criteria, $context)->getEntities();
    }
}
