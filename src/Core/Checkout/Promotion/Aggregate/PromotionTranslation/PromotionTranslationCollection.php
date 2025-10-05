<?php
declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PromotionTranslationEntity>
 */
#[Package('checkout')]
class PromotionTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'promotion_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return PromotionTranslationEntity::class;
    }
}
