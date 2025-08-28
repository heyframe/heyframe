<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\DataAbstractionLayer;

use Doctrine\DBAL\Query\QueryBuilder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;

/**
 * Allows project overrides to change cheapest price selection
 */
#[Package('framework')]
class CheapestPriceQuantitySelector extends AbstractCheapestPriceQuantitySelector
{
    public function getDecorated(): AbstractCheapestPriceQuantitySelector
    {
        throw new DecorationPatternException(self::class);
    }

    public function add(QueryBuilder $query): void
    {
        $query->addSelect(
            'price.quantity_start != 1 as is_ranged',
        );

        $query->andWhere('price.quantity_end IS NULL');
    }
}
