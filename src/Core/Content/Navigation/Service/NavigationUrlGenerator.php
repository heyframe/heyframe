<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Service;

use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('discovery')]
class NavigationUrlGenerator extends AbstractNavigationUrlGenerator
{
    /**
     * @internal
     */
    public function __construct(private readonly SeoUrlPlaceholderHandlerInterface $seoUrlReplacer)
    {
    }

    public function getDecorated(): AbstractNavigationUrlGenerator
    {
        throw new DecorationPatternException(self::class);
    }

    public function generate(NavigationEntity $navigation, ?ChannelEntity $channel): ?string
    {
        if ($navigation->getType() === NavigationDefinition::TYPE_FOLDER) {
            return null;
        }

        if ($navigation->getType() !== NavigationDefinition::TYPE_LINK) {
            return $this->seoUrlReplacer->generate('frontend.navigation.page', ['navigationId' => $navigation->getId()]);
        }

        $linkType = $navigation->getTranslation('linkType');
        $internalLink = $navigation->getTranslation('internalLink');

        if (!$internalLink && $linkType && $linkType !== NavigationDefinition::LINK_TYPE_EXTERNAL) {
            return null;
        }

        switch ($linkType) {
            case NavigationDefinition::LINK_TYPE_PRODUCT:
                return $this->seoUrlReplacer->generate('frontend.detail.page', ['productId' => $internalLink]);

            case NavigationDefinition::LINK_TYPE_CATEGORY:
                if ($channel !== null && $internalLink === $channel->getNavigationId()) {
                    return $this->seoUrlReplacer->generate('frontend.home.page');
                }

                return $this->seoUrlReplacer->generate('frontend.navigation.page', ['navigationId' => $internalLink]);

            case NavigationDefinition::LINK_TYPE_LANDING_PAGE:
                return $this->seoUrlReplacer->generate('frontend.landing.page', ['landingPageId' => $internalLink]);

            case NavigationDefinition::LINK_TYPE_EXTERNAL:
            default:
                return $navigation->getTranslation('externalLink');
        }
    }
}
