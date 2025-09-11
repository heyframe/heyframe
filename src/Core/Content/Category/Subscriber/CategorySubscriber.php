<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Subscriber;

use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Content\Category\CategoryEvents;
use HeyFrame\Core\Content\Category\Channel\ChannelCategoryEntity;
use HeyFrame\Core\Content\Category\Service\AbstractCategoryUrlGenerator;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Entity\ChannelEntityLoadedEvent;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('discovery')]
class CategorySubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly AbstractCategoryUrlGenerator $categoryUrlGenerator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CategoryEvents::CATEGORY_LOADED_EVENT => 'categoryLoaded',
            'channel.' . CategoryEvents::CATEGORY_LOADED_EVENT => 'channelCategoryLoaded',
        ];
    }

    /**
     * @param EntityLoadedEvent<covariant CategoryEntity> $event
     */
    public function categoryLoaded(EntityLoadedEvent $event): void
    {
        $systemDefaultLayout = $this->systemConfigService->getString(CategoryDefinition::CONFIG_KEY_DEFAULT_CMS_PAGE_CATEGORY);
        if ($systemDefaultLayout === '') {
            return;
        }

        foreach ($event->getEntities() as $category) {
            if (!$category->getCmsPageId()) {
                $category->setCmsPageId($systemDefaultLayout);
                $category->setCmsPageIdSwitched(true);
            }
        }
    }

    /**
     * @param ChannelEntityLoadedEvent<ChannelCategoryEntity> $event
     */
    public function channelCategoryLoaded(ChannelEntityLoadedEvent $event): void
    {
        $channel = $event->getChannelContext()->getChannel();
        $channelId = $channel->getId();

        $systemDefaultLayout = $this->systemConfigService->getString(CategoryDefinition::CONFIG_KEY_DEFAULT_CMS_PAGE_CATEGORY);
        $channelDefaultLayout = $this->systemConfigService->getString(CategoryDefinition::CONFIG_KEY_DEFAULT_CMS_PAGE_CATEGORY, $channelId);

        foreach ($event->getEntities() as $category) {
            $category->assign([
                'seoUrl' => $this->categoryUrlGenerator->generate($category, $channel),
            ]);

            if ($channelDefaultLayout === '') {
                continue;
            }

            // continue if layout is given and was not set in the `category.loaded` event and has not been modified in between
            if ($category->getCmsPageId() !== null && (!$category->getCmsPageIdSwitched() || $category->getCmsPageId() !== $systemDefaultLayout)) {
                continue;
            }

            $category->setCmsPageId($channelDefaultLayout);
            $category->setCmsPageIdSwitched(true);
        }
    }
}
