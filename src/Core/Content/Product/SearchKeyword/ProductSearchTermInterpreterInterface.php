<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\SearchKeyword;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Term\SearchPattern;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
interface ProductSearchTermInterpreterInterface
{
    public function interpret(string $word, Context $context): SearchPattern;
}
