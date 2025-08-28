<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Traits\Promotion;

use HeyFrame\Core\Checkout\Cart\Rule\LineItemRule;
use HeyFrame\Core\Checkout\Promotion\Aggregate\PromotionSetGroup\PromotionSetGroupEntity;
use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Content\Rule\RuleEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\TestDefaults;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('checkout')]
trait PromotionSetGroupTestFixtureBehaviour
{
    /**
     * @param RuleEntity[] $rules
     */
    private function createSetGroup(string $packagerKey, float $value, string $sorterKey, array $rules): PromotionSetGroupEntity
    {
        $group = new PromotionSetGroupEntity();
        $group->setId(Uuid::randomBytes());

        $group->setPackagerKey($packagerKey);
        $group->setValue($value);
        $group->setSorterKey($sorterKey);
        $group->setSetGroupRules(new RuleCollection($rules));

        return $group;
    }

    private function createSetGroupWithRuleFixture(string $groupId, string $packagerKey, float $value, string $sorterKey, string $promotionId, string $ruleId, ContainerInterface $container): string
    {
        $context = $container->get(ChannelContextFactory::class)->create(Uuid::randomHex(), TestDefaults::CHANNEL);

        $repository = $container->get('promotion_setgroup.repository');

        $data = [
            'id' => $groupId,
            'promotionId' => $promotionId,
            'packagerKey' => $packagerKey,
            'sorterKey' => $sorterKey,
            'value' => $value,
        ];

        $repository->create([$data], $context->getContext());

        $ruleRepository = $container->get('promotion_setgroup_rule.repository');

        $dataAssoc = [
            'setgroupId' => $groupId,
            'ruleId' => $ruleId,
        ];

        $ruleRepository->create([$dataAssoc], $context->getContext());

        return $groupId;
    }

    /**
     * @param array<string> $lineItemIds
     */
    private function createRule(string $name, array $lineItemIds, ContainerInterface $container): string
    {
        $context = $container->get(ChannelContextFactory::class)->create(Uuid::randomHex(), TestDefaults::CHANNEL);
        $ruleRepository = $container->get('rule.repository');
        $conditionRepository = $container->get('rule_condition.repository');

        $ruleId = Uuid::randomHex();
        $ruleRepository->create(
            [['id' => $ruleId, 'name' => $name, 'priority' => 1]],
            $context->getContext()
        );

        $id = Uuid::randomHex();
        $conditionRepository->create([
            [
                'id' => $id,
                'type' => (new LineItemRule())->getName(),
                'ruleId' => $ruleId,
                'value' => [
                    'identifiers' => $lineItemIds,
                    'operator' => Rule::OPERATOR_EQ,
                ],
            ],
        ], $context->getContext());

        return $ruleId;
    }
}
