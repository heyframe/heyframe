<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Service;

use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('discovery')]
class CategoryUrlGenerator extends AbstractCategoryUrlGenerator
{
    public function getDecorated(): AbstractCategoryUrlGenerator
    {
        throw new DecorationPatternException(self::class);
    }

    public function generate(CategoryEntity $category, ?ChannelEntity $channel): ?string
    {
        if ($category->getType() === CategoryDefinition::TYPE_FOLDER) {
            return null;
        }

        if ($category->getType() !== CategoryDefinition::TYPE_LINK) {
            return '/navigation/' . $category->getId();
        }

        $linkType = $category->getTranslation('linkType');
        $internalLink = $category->getTranslation('internalLink');

        if (!$internalLink && $linkType && $linkType !== CategoryDefinition::LINK_TYPE_EXTERNAL) {
            return null;
        }

        switch ($linkType) {
            case CategoryDefinition::LINK_TYPE_PRODUCT:
                return '/detail/' . $internalLink;

            case CategoryDefinition::LINK_TYPE_CATEGORY:
                if ($channel !== null && $internalLink === $channel->getNavigationId()) {
                    return '/';
                }

                return '/navigation/' . $internalLink;

            case CategoryDefinition::LINK_TYPE_LANDING_PAGE:
                return '/landing/' . $internalLink;

            case CategoryDefinition::LINK_TYPE_EXTERNAL:
            default:
                return $category->getTranslation('externalLink');
        }
    }
}
