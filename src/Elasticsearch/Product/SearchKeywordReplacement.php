<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Product;

use HeyFrame\Core\Content\Product\DataAbstractionLayer\SearchKeywordUpdater;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Elasticsearch\Framework\ElasticsearchHelper;

#[Package('framework')]
class SearchKeywordReplacement extends SearchKeywordUpdater
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SearchKeywordUpdater $decorated,
        private readonly ElasticsearchHelper $helper
    ) {
    }

    /**
     * @param array<string> $ids
     */
    public function update(array $ids, Context $context): void
    {
        if ($this->helper->allowIndexing()) {
            return;
        }

        $this->decorated->update($ids, $context);
    }

    public function reset(): void
    {
        $this->decorated->reset();
    }
}
