<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Cart\Rule;

use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Rule\CartRuleScope;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemPurchasePriceRule;
use HeyFrame\Core\Checkout\Cart\Rule\LineItemScope;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\Price;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Tests\Unit\Core\Checkout\Cart\Channel\Helper\CartRuleHelperTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
#[Group('rules')]
class LineItemPurchasePriceRuleTest extends TestCase
{
    use CartRuleHelperTrait;
    use IntegrationTestBehaviour;

    private LineItemPurchasePriceRule $rule;

    protected function setUp(): void
    {
        $this->rule = new LineItemPurchasePriceRule();
    }

    public function testGetName(): void
    {
        static::assertSame('cartLineItemPurchasePrice', $this->rule->getName());
    }

    public function testGetConstraints(): void
    {
        $ruleConstraints = $this->rule->getConstraints();

        static::assertArrayHasKey('amount', $ruleConstraints, 'Rule Constraint amount is not defined');
        static::assertArrayHasKey('operator', $ruleConstraints, 'Rule Constraint operator is not defined');
    }

    public function testValidateWithInvalidValue(): void
    {
        try {
            static::getContainer()->get('rule_condition.repository')->create([
                [
                    'type' => $this->rule->getName(),
                    'ruleId' => Uuid::randomHex(),
                    'value' => [
                        'operator' => '===',
                        'amount' => 'foobar',
                    ],
                ],
            ], Context::createDefaultContext());
            static::fail('Exception was not thrown');
        } catch (WriteException $stackException) {
            static::assertGreaterThan(0, \count($stackException->getExceptions()));
            $exceptions = iterator_to_array($stackException->getErrors());

            static::assertCount(2, $exceptions);

            static::assertSame('/0/value/operator', $exceptions[0]['source']['pointer']);
            static::assertSame(Choice::NO_SUCH_CHOICE_ERROR, $exceptions[0]['code']);

            static::assertSame('/0/value/amount', $exceptions[1]['source']['pointer']);
            static::assertSame(Type::INVALID_TYPE_ERROR, $exceptions[1]['code']);
        }
    }

    #[DataProvider('getMatchingRuleTestData')]
    public function testIfMatchesCorrectWithLineItemPurchasePriceGross(
        string $operator,
        ?float $amount,
        ?float $lineItemPurchasePriceGross,
        bool $expected,
        bool $noPrice = false
    ): void {
        $this->rule->assign([
            'isNet' => false,
            'amount' => $amount,
            'operator' => $operator,
        ]);

        $lineItem = $this->createLineItem();
        if ($lineItemPurchasePriceGross !== null && !$noPrice) {
            $lineItem = $this->createLineItemWithPurchasePrice(0, $lineItemPurchasePriceGross);
        }

        $match = $this->rule->match(new LineItemScope(
            $lineItem,
            $this->createMock(ChannelContext::class)
        ));

        static::assertSame($expected, $match);
    }

    #[DataProvider('getMatchingRuleTestData')]
    public function testIfMatchesCorrectWithLineItemPurchasePriceNet(
        string $operator,
        ?float $amount,
        ?float $lineItemPurchasePriceNet,
        bool $expected,
        bool $noPrice = false
    ): void {
        $this->rule->assign([
            'isNet' => true,
            'amount' => $amount,
            'operator' => $operator,
        ]);

        if ($lineItemPurchasePriceNet === null) {
            $lineItem = $this->createLineItem();
            $lineItem->setPayloadValue('purchasePrices', null);
        } else {
            $lineItem = $this->createLineItemWithPurchasePrice($lineItemPurchasePriceNet);
            if ($noPrice) {
                $lineItem = $this->createLineItem();
            }
        }

        $match = $this->rule->match(new LineItemScope(
            $lineItem,
            $this->createMock(ChannelContext::class)
        ));

        static::assertSame($expected, $match);
    }

    /**
     * @return \Traversable<string, array<string|int|bool|null>>
     */
    public static function getMatchingRuleTestData(): \Traversable
    {
        // OPERATOR_EQ
        yield 'no match / operator equals / different price' => [Rule::OPERATOR_EQ, 200, 100, false];
        yield 'no match / operator equals / no price' => [Rule::OPERATOR_EQ, 200, 100, false, true];
        // OPERATOR_NEQ
        yield 'match / operator not equals / different price' => [Rule::OPERATOR_NEQ, 200, 100, true];
        // OPERATOR_GT
        yield 'no match / operator greater than / lower price' => [Rule::OPERATOR_GT, 100, 50, false];
        yield 'no match / operator greater than / same price' => [Rule::OPERATOR_GT, 100, 100, false];
        // OPERATOR_GTE
        yield 'no match / operator greater than equals / lower price' => [Rule::OPERATOR_GTE, 100, 50, false];
        // OPERATOR_LT
        yield 'match / operator lower than / lower price' => [Rule::OPERATOR_LT, 100, 50, true];
        // OPERATOR_LTE
        yield 'match / operator lower than equals / lower price' => [Rule::OPERATOR_LTE, 100, 50, true];
        yield 'match / operator lower than equals / same price' => [Rule::OPERATOR_LTE, 100, 100, true];
        // OPERATOR_EMPTY
        yield 'match / operator empty / no price' => [Rule::OPERATOR_EMPTY, 100, 200, true, true];
        yield 'match / operator empty / with empty price' => [Rule::OPERATOR_EMPTY, null, null, true];
        yield 'no match / operator empty / with price' => [Rule::OPERATOR_EMPTY, 100, 200, false];

        yield 'match / operator not equals / no price' => [Rule::OPERATOR_NEQ, 200, 100, true, true];
        yield 'match / operator empty / with only empty rule price' => [Rule::OPERATOR_EMPTY, null, 100, false];
    }

    #[DataProvider('getCartRuleScopeTestData')]
    public function testIfMatchesCorrectWithCartRuleScopePurchasePrice(
        string $operator,
        float $amount,
        float $lineItemPurchasePrice1,
        float $lineItemPurchasePrice2,
        bool $expected,
        bool $lineItem1WithoutPrice = false,
        bool $lineItem2WithoutPrice = false
    ): void {
        $this->rule->assign([
            'amount' => $amount,
            'operator' => $operator,
        ]);

        $lineItem1 = $this->createLineItemWithPurchasePrice($lineItemPurchasePrice1);
        if ($lineItem1WithoutPrice) {
            $lineItem1 = $this->createLineItem();
        }

        $lineItem2 = $this->createLineItemWithPurchasePrice($lineItemPurchasePrice2);
        if ($lineItem2WithoutPrice) {
            $lineItem2 = $this->createLineItem();
        }

        $lineItemCollection = new LineItemCollection([
            $lineItem1,
            $lineItem2,
        ]);
        $cart = $this->createCart($lineItemCollection);

        $match = $this->rule->match(new CartRuleScope(
            $cart,
            $this->createMock(ChannelContext::class)
        ));

        static::assertSame($expected, $match);
    }

    #[DataProvider('getCartRuleScopeTestData')]
    public function testIfMatchesCorrectWithCartRuleScopePurchasePriceNested(
        string $operator,
        float $amount,
        float $lineItemPurchasePrice1,
        float $lineItemPurchasePrice2,
        bool $expected,
        bool $lineItem1WithoutPrice = false,
        bool $lineItem2WithoutPrice = false,
        ?float $containerLineItemPrice = null
    ): void {
        $this->rule->assign([
            'isNet' => true,
            'amount' => $amount,
            'operator' => $operator,
        ]);

        $lineItem1 = $this->createLineItemWithPurchasePrice($lineItemPurchasePrice1);
        if ($lineItem1WithoutPrice) {
            $lineItem1 = $this->createLineItem();
        }

        $lineItem2 = $this->createLineItemWithPurchasePrice($lineItemPurchasePrice2);
        if ($lineItem2WithoutPrice) {
            $lineItem2 = $this->createLineItem();
        }

        $lineItemCollection = new LineItemCollection([
            $lineItem1,
            $lineItem2,
        ]);

        $containerLineItem = $this->createLineItem();
        if ($containerLineItemPrice !== null) {
            $containerLineItem = $this->createLineItemWithPurchasePrice($containerLineItemPrice);
        }
        $containerLineItem->setType(LineItem::CONTAINER_LINE_ITEM);
        $containerLineItem->setChildren($lineItemCollection);
        $cart = $this->createCart(new LineItemCollection([$containerLineItem]));

        $match = $this->rule->match(new CartRuleScope(
            $cart,
            $this->createMock(ChannelContext::class)
        ));

        static::assertSame($expected, $match);
    }

    /**
     * @return \Traversable<string, array<string|int|bool|null>>
     */
    public static function getCartRuleScopeTestData(): \Traversable
    {
        // OPERATOR_EQ
        yield 'no match / operator equals / different price' => [Rule::OPERATOR_EQ, 200, 100, 300, false];
        // OPERATOR_NEQ
        yield 'match / operator not equals / different prices' => [Rule::OPERATOR_NEQ, 200, 100, 200, true];
        yield 'match / operator not equals / different prices 2' => [Rule::OPERATOR_NEQ, 200, 100, 300, true];
        // OPERATOR_GT
        yield 'no match / operator greater than / lower price' => [Rule::OPERATOR_GT, 100, 50, 70, false];
        yield 'no match / operator greater than / same price' => [Rule::OPERATOR_GT, 100, 100, 70, false];
        // OPERATOR_GTE
        yield 'no match / operator greater than equals / lower price' => [Rule::OPERATOR_GTE, 100, 50, 70, false];
        // OPERATOR_LT
        yield 'match / operator lower than / lower price' => [Rule::OPERATOR_LT, 100, 50, 120, true];
        // OPERATOR_LTE
        yield 'match / operator lower than equals / lower price' => [Rule::OPERATOR_LTE, 100, 50, 120, true];
        yield 'match / operator lower than equals / same price' => [Rule::OPERATOR_LTE, 100, 100, 120, true];

        yield 'match / operator empty / item 1 and 2 without price' => [Rule::OPERATOR_EMPTY, 200, 100, 300, true, true, true];
        yield 'match / operator empty / item 1 without price' => [Rule::OPERATOR_EMPTY, 100, 100, 100, true, true];
        yield 'match / operator empty / item 2 without price' => [Rule::OPERATOR_EMPTY, 100, 100, 100, true, false, true];

        yield 'match / operator not equals / item 1 and 2 without price' => [Rule::OPERATOR_NEQ, 200, 100, 300, true, true, true];
        yield 'match / operator not equals / item 1 without price' => [Rule::OPERATOR_NEQ, 100, 100, 100, true, true];
        yield 'match / operator not equals / item 2 without price' => [Rule::OPERATOR_NEQ, 100, 100, 100, true, false, true];
    }

    public function testMatchWithEmptyPurchasePricePayload(): void
    {
        $this->rule->assign(['amount' => 100, 'operator' => Rule::OPERATOR_EQ]);

        $match = $this->rule->match(new LineItemScope(
            $this->createLineItem(),
            $this->createMock(ChannelContext::class)
        ));

        static::assertFalse($match);
    }

    private function createLineItemWithPurchasePrice(
        float $purchasePriceNet = 0,
        float $purchasePriceGross = 0
    ): LineItem {
        return $this->createLineItem()->setPayloadValue(
            'purchasePrices',
            json_encode(new Price(
                Defaults::CURRENCY,
                $purchasePriceGross,
            ), \JSON_THROW_ON_ERROR)
        );
    }
}
