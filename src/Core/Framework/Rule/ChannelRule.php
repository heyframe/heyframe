<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Rule;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

/**
 * @final
 */
#[Package('fundamentals@after-sales')]
class ChannelRule extends Rule
{
    final public const RULE_NAME = 'channel';

    /**
     * @param list<string>|null $channelIds
     *
     * @internal
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?array $channelIds = null
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        return RuleComparison::uuids([$scope->getChannelContext()->getChannelId()], $this->channelIds, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'channelIds' => RuleConstraints::uuids(),
            'operator' => RuleConstraints::uuidOperators(false),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, false, true)
            ->entitySelectField('channelIds', ChannelDefinition::ENTITY_NAME, true);
    }
}
