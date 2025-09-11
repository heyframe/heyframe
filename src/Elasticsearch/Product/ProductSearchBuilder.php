<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Product;

use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\RoutingException;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Elasticsearch\Framework\ElasticsearchHelper;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class ProductSearchBuilder implements ProductSearchBuilderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ProductSearchBuilderInterface $decorated,
        private readonly ElasticsearchHelper $helper,
        private readonly ProductDefinition $productDefinition,
        private readonly int $searchTermMaxLength = 300
    ) {
    }

    public function build(Request $request, Criteria $criteria, ChannelContext $context): void
    {
        if (!$this->helper->allowSearch($this->productDefinition, $context->getContext(), $criteria)) {
            $this->decorated->build($request, $criteria, $context);

            return;
        }

        $search = $request->get('search');

        if (\is_array($search)) {
            $term = implode(' ', $search);
        } else {
            $term = (string) $search;
        }

        $term = mb_substr(trim($term), 0, $this->searchTermMaxLength);

        if (empty($term)) {
            throw RoutingException::missingRequestParameter('search');
        }

        // reset queries and set term to criteria.
        $criteria->resetQueries();

        // elasticsearch will interpret this on demand
        $criteria->setTerm($term);
    }
}
