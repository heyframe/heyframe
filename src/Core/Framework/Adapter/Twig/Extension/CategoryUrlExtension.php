<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\Extension;

use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Content\Category\Channel\ChannelCategoryEntity;
use HeyFrame\Core\Content\Category\Service\AbstractCategoryUrlGenerator;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @deprecated tag:v6.8.0 - reason:remove-subscriber - Will be removed, use CategoryEntity directly
 */
#[Package('framework')]
class CategoryUrlExtension extends AbstractExtension
{
    /**
     * @internal
     */
    public function __construct(
        private readonly RoutingExtension $routingExtension,
        private readonly AbstractCategoryUrlGenerator $categoryUrlGenerator
    ) {
    }

    public function getFunctions(): array
    {
        if (Feature::isActive('v6.8.0.0')) {
            return [];
        }

        return [
            new TwigFunction('category_url', $this->getCategoryUrl(...), ['needs_context' => true, 'is_safe_callback' => $this->routingExtension->isUrlGenerationSafe(...)]),
            new TwigFunction('category_linknewtab', $this->isLinkNewTab(...)),
        ];
    }

    /**
     * @param array<string, mixed> $twigContext
     */
    public function getCategoryUrl(array $twigContext, CategoryEntity $category): ?string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            'The "category_url" function is deprecated and will be removed in v6.8.0.0. Use ChannelCategoryEntity::getSeoUrl() instead.'
        );

        if ($category instanceof ChannelCategoryEntity) {
            return $category->getSeoUrl();
        }

        $channel = null;
        if (\array_key_exists('context', $twigContext) && $twigContext['context'] instanceof ChannelContext) {
            $channel = $twigContext['context']->getChannel();
        }

        return $this->categoryUrlGenerator->generate($category, $channel);
    }

    public function isLinkNewTab(CategoryEntity $categoryEntity): bool
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            'The "category_linknewtab" function is deprecated and will be removed in v6.8.0.0. Use CategoryEntity::shouldOpenInNewTab() instead.'
        );

        return $categoryEntity->shouldOpenInNewTab();
    }
}
