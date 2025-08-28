<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\SearchKeyword;

use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
interface ProductSearchKeywordAnalyzerInterface
{
    /**
     * @param array<int, array{field: string, tokenize: '1'|'0'|bool, ranking: numeric-string|int|float}> $configFields
     */
    public function analyze(ProductEntity $product, Context $context, array $configFields): AnalyzedKeywordCollection;
}
