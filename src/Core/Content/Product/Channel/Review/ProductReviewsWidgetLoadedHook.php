<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review;

use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\ChannelRepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAwareTrait;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\Facade\SystemConfigFacadeHookFactory;

/**
 * Triggered when the ProductReviewsWidget is loaded
 *
 * @hook-use-case data_loading
 *
 * @since 6.6.9.0
 *
 * @final
 */
#[Package('after-sales')]
class ProductReviewsWidgetLoadedHook extends Hook implements ChannelContextAware
{
    use ChannelContextAwareTrait;

    final public const HOOK_NAME = 'product-reviews-widget-loaded';

    public function __construct(
        private readonly ProductReviewResult $reviews,
        ChannelContext $context
    ) {
        parent::__construct($context->getContext());
        $this->channelContext = $context;
    }

    public static function getServiceIds(): array
    {
        return [
            RepositoryFacadeHookFactory::class,
            SystemConfigFacadeHookFactory::class,
            ChannelRepositoryFacadeHookFactory::class,
        ];
    }

    public function getName(): string
    {
        return self::HOOK_NAME;
    }

    public function getReviews(): ProductReviewResult
    {
        return $this->reviews;
    }
}
