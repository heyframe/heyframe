<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Gateway\Template;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use HeyFrame\Core\Framework\Log\Package;
use Psr\Clock\ClockInterface;

/**
 * @final
 */
#[Package('checkout')]
class ActiveDateRange extends MultiFilter
{
    /**
     * Creates a new date range filter that only matches
     * for promotions that have a valid date range right now.
     * This means either no date ranges set at all, either no starting
     * or ending date, or a valid and active date range.
     */
    public function __construct(?ClockInterface $clock = null)
    {
        $dateTime = $clock?->now() ?? new \DateTime();
        $today = $dateTime->setTimezone(new \DateTimeZone('UTC'));

        $todayStart = $today->format('Y-m-d H:i:s');
        $todayEnd = $today->format('Y-m-d H:i:s');

        $filterNoDateRange = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('validFrom', null),
                new EqualsFilter('validUntil', null),
            ]
        );

        $filterStartedNoEndDate = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new RangeFilter('validFrom', ['lte' => $todayStart]),
                new EqualsFilter('validUntil', null),
            ]
        );

        $filterActiveNoStartDate = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new EqualsFilter('validFrom', null),
                new RangeFilter('validUntil', ['gt' => $todayEnd]),
            ]
        );

        $activeDateRangeFilter = new MultiFilter(
            MultiFilter::CONNECTION_AND,
            [
                new RangeFilter('validFrom', ['lte' => $todayStart]),
                new RangeFilter('validUntil', ['gt' => $todayEnd]),
            ]
        );

        parent::__construct(
            MultiFilter::CONNECTION_OR,
            [
                $filterNoDateRange,
                $filterActiveNoStartDate,
                $filterStartedNoEndDate,
                $activeDateRangeFilter,
            ]
        );
    }
}
