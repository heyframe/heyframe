<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Review\Event;

use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\ChannelAware;
use HeyFrame\Core\Framework\Event\CustomerAware;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\Framework\Event\ProductAware;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('after-sales')]
final class ReviewFormEvent extends Event implements ChannelAware, ProductAware, CustomerAware, FlowEventAware
{
    public const EVENT_NAME = 'review_form.send';

    /**
     * @var array<int|string, mixed>
     */
    private readonly array $reviewFormData;

    public function __construct(
        private readonly Context $context,
        private readonly string $channelId,
        DataBag $reviewFormData,
        private readonly string $productId,
        private readonly string $customerId
    ) {
        $this->reviewFormData = $reviewFormData->all();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add(ProductAware::PRODUCT, new EntityType(ProductDefinition::class));
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function getReviewFormData(): array
    {
        return $this->reviewFormData;
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }
}
