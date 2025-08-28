<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Builder\Promotion;

use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountCollection;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroup\PromotionSetGroupCollection;
use HeyFrame\Core\Checkout\Promotion\PromotionCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\AbstractChannelContextFactory;
use HeyFrame\Core\Test\TestDefaults;

/**
 * @internal
 */
#[Package('checkout')]
class PromotionFixtureBuilder
{
    private readonly ChannelContext $context;

    private ?string $code = null;

    /**
     * @var list<array<string, mixed>>
     */
    private array $dataSetGroups = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $dataDiscounts = [];

    /**
     * @param EntityRepository<PromotionCollection> $promotionRepository
     * @param EntityRepository<PromotionSetGroupCollection> $promotionSetgroupRepository
     * @param EntityRepository<PromotionDiscountCollection> $promotionDiscountRepository
     */
    public function __construct(
        private readonly string $promotionId,
        AbstractChannelContextFactory $channelContextFactory,
        private readonly EntityRepository $promotionRepository,
        private readonly EntityRepository $promotionSetgroupRepository,
        private readonly EntityRepository $promotionDiscountRepository
    ) {
        $this->context = $channelContextFactory->create(Uuid::randomHex(), TestDefaults::CHANNEL);
    }

    public function setCode(string $code): PromotionFixtureBuilder
    {
        $this->code = $code;

        return $this;
    }

    public function addDiscount(
        string $scope,
        string $type,
        float $value,
        bool $considerAdvancedRules,
        ?float $maxValue
    ): PromotionFixtureBuilder {
        $data = [
            'id' => Uuid::randomHex(),
            'promotionId' => $this->promotionId,
            'scope' => $scope,
            'type' => $type,
            'value' => $value,
            'considerAdvancedRules' => $considerAdvancedRules,
        ];

        if ($maxValue !== null) {
            $data['maxValue'] = $maxValue;
        }

        $this->dataDiscounts[] = $data;

        return $this;
    }

    public function addSetGroup(string $packagerKey, float $value, string $sorterKey): PromotionFixtureBuilder
    {
        $this->dataSetGroups[] = [
            'id' => Uuid::randomHex(),
            'promotionId' => $this->promotionId,
            'packagerKey' => $packagerKey,
            'sorterKey' => $sorterKey,
            'value' => $value,
        ];

        return $this;
    }

    /**
     * Builds our configured promotion and saves all related
     * entities and objects in the database.
     */
    public function buildPromotion(): void
    {
        $data = [
            'id' => $this->promotionId,
            'name' => 'Black Friday',
            'active' => true,
            'useCodes' => false,
            'useSetGroups' => false,
            'channels' => [
                ['channelId' => TestDefaults::CHANNEL, 'priority' => 1],
            ],
        ];

        if ($this->code !== null) {
            $data['code'] = $this->code;
            $data['useCodes'] = true;
        }

        if (\count($this->dataSetGroups) > 0) {
            $data['useSetGroups'] = true;
        }

        // save the promotion
        $this->promotionRepository->create([$data], $this->context->getContext());

        // save our defined set groups
        if (\count($this->dataSetGroups) > 0) {
            $this->promotionSetgroupRepository->create($this->dataSetGroups, $this->context->getContext());
        }

        // save our added discounts
        if (\count($this->dataDiscounts) > 0) {
            $this->promotionDiscountRepository->create($this->dataDiscounts, $this->context->getContext());
        }
    }
}
