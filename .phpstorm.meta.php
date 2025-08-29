<?php

namespace PHPSTORM_META {
    expectedArguments(
        \HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria::setTotalCountMode(),
        0,
        \HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria::TOTAL_COUNT_MODE_NONE,
        \HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria::TOTAL_COUNT_MODE_EXACT,
        \HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria::TOTAL_COUNT_MODE_NEXT_PAGES
    );

    expectedArguments(
        \HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting::__construct(),
        1,
        \HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting::ASCENDING,
        \HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting::DESCENDING
    );

}
