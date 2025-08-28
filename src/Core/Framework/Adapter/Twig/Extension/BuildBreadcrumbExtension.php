<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\Extension;

use HeyFrame\Core\Content\Category\CategoryCollection;
use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Content\Category\Channel\ChannelCategoryEntity;
use HeyFrame\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Package('framework')]
class BuildBreadcrumbExtension extends AbstractExtension
{
    /**
     * @internal
     *
     * @param ChannelRepository<EntityCollection<ChannelCategoryEntity>> $channelCategoryRepository
     * @param EntityRepository<CategoryCollection> $categoryRepository
     */
    public function __construct(
        private readonly CategoryBreadcrumbBuilder $categoryBreadcrumbBuilder,
        private readonly ChannelRepository $channelCategoryRepository,
        private readonly EntityRepository $categoryRepository,
    ) {
    }

    public function getFunctions(): array
    {
        /** @deprecated tag:v6.8.0 - Remove `needs_context` option, as the ChannelContext is required and the Twig Context is not needed anymore */
        return [
            new TwigFunction('sw_breadcrumb_full', $this->getFullBreadcrumb(...), ['needs_context' => true]),
            new TwigFunction('sw_breadcrumb_full_by_id', $this->getFullBreadcrumbById(...), ['needs_context' => true]),
        ];
    }

    /**
     * @deprecated tag:v6.8.0 - Parameter $twigContext will be removed, as it is not needed anymore and the type of `$context` will be changed to `ChannelContext`
     * @deprecated tag:v6.8.0 - reason:return-type-change - Will only return `array<string, ChannelCategoryEntity>`
     *
     * @param array<string, mixed> $twigContext
     *
     * @return array<string, CategoryEntity|ChannelCategoryEntity>
     */
    public function getFullBreadcrumb(array $twigContext, CategoryEntity $category, Context|ChannelContext $context): array
    {
        if (Feature::isActive('v6.8.0.0')) {
            \assert($context instanceof ChannelContext);

            $seoBreadcrumb = $this->categoryBreadcrumbBuilder->build($category, $context->getChannel());
        } else {
            if ($context instanceof Context) {
                Feature::triggerDeprecationOrThrow(
                    'v6.8.0.0',
                    'Passing the Context to getFullBreadcrumb is deprecated. The ChannelContext will be required in v6.8.0.0.'
                );

                $context = $this->getChannelContext($twigContext) ?? $context;
            }

            $seoBreadcrumb = $this->categoryBreadcrumbBuilder->build(
                $category,
                ($context instanceof ChannelContext) ? $context->getChannel() : null,
            );
        }

        if ($seoBreadcrumb === null) {
            return [];
        }

        $categoryIds = array_keys($seoBreadcrumb);
        if (empty($categoryIds)) {
            return [];
        }

        $criteria = new Criteria($categoryIds);
        $criteria->setTitle('breadcrumb-extension');

        if (Feature::isActive('v6.8.0.0')) {
            \assert($context instanceof ChannelContext);

            $categories = $this->channelCategoryRepository->search($criteria, $context)->getEntities();
        } else {
            if ($context instanceof ChannelContext) {
                $categories = $this->channelCategoryRepository->search($criteria, $context)->getEntities();
            } else {
                $categories = $this->categoryRepository->search($criteria, $context)->getEntities();
            }
        }

        $breadcrumb = [];
        foreach ($categoryIds as $categoryId) {
            if ($categories->get($categoryId) === null) {
                continue;
            }

            $breadcrumb[$categoryId] = $categories->get($categoryId);
        }

        return $breadcrumb;
    }

    /**
     * @deprecated tag:v6.8.0 - Parameter $twigContext will be removed, as it is not needed anymore and the type of `$context` will be changed to `ChannelContext`
     * @deprecated tag:v6.8.0 - reason:return-type-change - Will only return `array<string, ChannelCategoryEntity>`
     *
     * @param array<string, mixed> $twigContext
     *
     * @return array<string, CategoryEntity|ChannelCategoryEntity>
     */
    public function getFullBreadcrumbById(array $twigContext, string $categoryId, Context|ChannelContext $context): array
    {
        if (Feature::isActive('v6.8.0.0')) {
            \assert($context instanceof ChannelContext);

            $category = $this->channelCategoryRepository->search(new Criteria([$categoryId]), $context)->getEntities()->first();
        } else {
            if ($context instanceof Context) {
                Feature::triggerDeprecationOrThrow(
                    'v6.8.0.0',
                    'Passing the Context to getFullBreadcrumbById is deprecated. The ChannelContext will be required in v6.8.0.0.'
                );

                $context = $this->getChannelContext($twigContext) ?? $context;
            }

            if ($context instanceof ChannelContext) {
                $category = $this->channelCategoryRepository->search(new Criteria([$categoryId]), $context)->getEntities()->first();
            } else {
                $category = $this->categoryRepository->search(new Criteria([$categoryId]), $context)->getEntities()->first();
            }
        }

        if ($category === null) {
            return [];
        }

        return $this->getFullBreadcrumb($twigContext, $category, $context);
    }

    /**
     * @param array<string, mixed> $twigContext
     */
    private function getChannelContext(array $twigContext): ?ChannelContext
    {
        $context = $twigContext['context'] ?? null;
        if ($context instanceof ChannelContext) {
            return $context;
        }

        return null;
    }
}
