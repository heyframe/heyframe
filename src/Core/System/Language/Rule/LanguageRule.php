<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Language\Rule;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Rule\Exception\UnsupportedOperatorException;
use HeyFrame\Core\Framework\Rule\Exception\UnsupportedValueException;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\RuleComparison;
use HeyFrame\Core\Framework\Rule\RuleConfig;
use HeyFrame\Core\Framework\Rule\RuleConstraints;
use HeyFrame\Core\Framework\Rule\RuleScope;
use HeyFrame\Core\System\Language\LanguageDefinition;
use HeyFrame\Core\System\Language\LanguageException;

/**
 * @final
 */
class LanguageRule extends Rule
{
    final public const RULE_NAME = 'language';

    /**
     * @internal
     *
     * @param list<string>|null $languageIds
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?array $languageIds = null
    ) {
        parent::__construct();
    }

    /**
     * @throws UnsupportedOperatorException|UnsupportedValueException|CustomerException
     */
    public function match(RuleScope $scope): bool
    {
        if ($this->languageIds === null) {
            throw LanguageException::unsupportedValue(\gettype($this->languageIds), self::class);
        }

        return RuleComparison::uuids([$scope->getContext()->getLanguageId()], $this->languageIds, $this->operator);
    }

    public function getConstraints(): array
    {
        return [
            'operator' => RuleConstraints::uuidOperators(false),
            'languageIds' => RuleConstraints::uuids(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, false, true)
            ->entitySelectField('languageIds', LanguageDefinition::ENTITY_NAME, true);
    }
}
