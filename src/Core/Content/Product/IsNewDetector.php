<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;

#[Package('inventory')]
class IsNewDetector extends AbstractIsNewDetector
{
    /**
     * @internal
     */
    public function __construct(private readonly SystemConfigService $systemConfigService)
    {
    }

    public function getDecorated(): AbstractIsNewDetector
    {
        throw new DecorationPatternException(self::class);
    }

    public function isNew(Entity $product, ChannelContext $context): bool
    {
        $markAsNewDayRange = $this->systemConfigService->get(
            'core.listing.markAsNew',
            $context->getChannelId()
        );

        $now = new \DateTime();

        /** @var \DateTimeInterface|null $releaseDate */
        $releaseDate = $product->get('releaseDate');

        return $releaseDate instanceof \DateTimeInterface
            && $releaseDate->diff($now)->days <= $markAsNewDayRange;
    }
}
