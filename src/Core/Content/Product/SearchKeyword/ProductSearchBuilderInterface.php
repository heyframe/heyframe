<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\SearchKeyword;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
interface ProductSearchBuilderInterface
{
    public function build(Request $request, Criteria $criteria, ChannelContext $context): void;
}
