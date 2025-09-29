<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\Extension;

use HeyFrame\Core\Content\Navigation\Channel\ChannelNavigationEntity;
use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Content\Navigation\Service\NavigationBreadcrumbBuilder;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Package('framework')]
class BuildBreadcrumbExtension extends AbstractExtension
{
    /**
     * @param ChannelRepository<EntityCollection<ChannelNavigationEntity>> $channelNavigationRepository
     * @param EntityRepository<NavigationCollection> $navigationRepository
     *
     * @internal
     */
    public function __construct(
        private readonly NavigationBreadcrumbBuilder $navigationBreadcrumbBuilder,
        private readonly ChannelRepository $channelNavigationRepository,
        private readonly EntityRepository $navigationRepository,
    ) {
    }

    public function getFunctions(): array
    {
        /** @deprecated tag:v6.8.0 - Remove `needs_context` option, as the ChannelContext is required and the Twig Context is not needed anymore */
        return [
            new TwigFunction('sw_breadcrumb_full', $this->getFullBreadcrumb(...)),
            new TwigFunction('sw_breadcrumb_full_by_id', $this->getFullBreadcrumbById(...)),
        ];
    }

    /**
     * @param array<string, mixed> $twigContext
     *
     * @return array<string, ChannelNavigationEntity>
     */
    public function getFullBreadcrumb(array $twigContext, NavigationEntity $navigation, ChannelContext $context): array
    {
        \assert($context instanceof ChannelContext);

        $seoBreadcrumb = $this->navigationBreadcrumbBuilder->build($navigation, $context->getChannel());

        if ($seoBreadcrumb === null) {
            return [];
        }

        $navigationIds = array_keys($seoBreadcrumb);
        if (empty($navigationIds)) {
            return [];
        }

        $criteria = new Criteria($navigationIds);
        $criteria->setTitle('breadcrumb-extension');

        \assert($context instanceof ChannelContext);

        $categories = $this->channelNavigationRepository->search($criteria, $context)->getEntities();

        $breadcrumb = [];
        foreach ($navigationIds as $navigationId) {
            if ($categories->get($navigationId) === null) {
                continue;
            }

            $breadcrumb[$navigationId] = $categories->get($navigationId);
        }

        return $breadcrumb;
    }

    /**
     * @param array<string, mixed> $twigContext
     *
     * @return array<string, ChannelNavigationEntity>
     */
    public function getFullBreadcrumbById(array $twigContext, string $navigationId, ChannelContext $context): array
    {
        \assert($context instanceof ChannelContext);

        $navigation = $this->channelNavigationRepository->search(new Criteria([$navigationId]), $context)->getEntities()->first();

        if ($navigation === null) {
            return [];
        }

        return $this->getFullBreadcrumb($twigContext, $navigation, $context);
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
