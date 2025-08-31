<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;

#[Package('framework')]
class DictLoader extends AbstractDictLoader
{
    /**
     * @internal
     *
     * @param EntityRepository<DictCollection> $dictRepository
     */
    public function __construct(
        private readonly EntityRepository $dictRepository,
    ) {
    }

    public function getDecorated(): AbstractDictLoader
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(?string $key, Context $context): DictCollection
    {
        $criteria = (new Criteria())
           ->addAssociation('items')
           ->addFilter(new EqualsFilter('active', true));

        if ($key !== null) {
            $criteria->addFilter(new EqualsFilter('key', $key));
        }

        /** @var DictCollection $collection */
        $collection = $this->dictRepository->search($criteria, $context)->getEntities();

        return $collection;
    }
}
