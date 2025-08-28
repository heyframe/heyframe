<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Gateway;

use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @final
 */
#[Package('checkout')]
class PromotionGateway implements PromotionGatewayInterface
{
    /**
     * @internal
     *
     * @param EntityRepository<PromotionCollection> $promotionRepository
     */
    public function __construct(private readonly EntityRepository $promotionRepository)
    {
    }

    /**
     * Gets a list of promotions for the provided criteria and
     * sales channel context.
     */
    public function get(Criteria $criteria, ChannelContext $context): PromotionCollection
    {
        $criteria->setTitle('cart::promotion');
        $criteria->addSorting(
            new FieldSorting('priority', FieldSorting::DESCENDING)
        );

        return $this->promotionRepository->search($criteria, $context->getContext())->getEntities();
    }
}
