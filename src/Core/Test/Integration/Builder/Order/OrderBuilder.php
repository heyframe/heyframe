<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Builder\Order;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\BasicTestDataBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestBuilderTrait;
use HeyFrame\Core\Test\TestDefaults;

/**
 * @final
 */
#[Package('checkout')]
class OrderBuilder
{
    use BasicTestDataBehaviour;
    use KernelTestBehaviour;
    use TestBuilderTrait;

    protected string $id;

    protected string $orderNumber;

    protected string $currencyId = Defaults::CURRENCY;

    protected float $currencyFactor = 1.0;

    protected string $billingAddressId;

    protected string $orderDateTime;

    protected CartPrice $price;

    protected CalculatedPrice $shippingCosts;

    /**
     * @var array<mixed>
     */
    protected array $lineItems = [];

    /**
     * @var array<mixed>
     */
    protected array $transactions = [];

    /**
     * @var array<mixed>
     */
    protected array $addresses = [];

    protected string $stateId;

    /**
     * @var array{id: string, orderId: string, customerId: string, versionId: string, orderVersionId: string, firstName: string, lastName: string, email: string}|null
     */
    protected ?array $orderCustomer = null;

    public function __construct(
        IdsCollection $ids,
        string $orderNumber,
        protected string $channelId = TestDefaults::CHANNEL
    ) {
        $this->ids = $ids;
        $this->id = $ids->get($orderNumber);
        $this->billingAddressId = $ids->get('billing_address');
        $this->stateId = $this->getStateMachineState();
        $this->orderNumber = $orderNumber;
        $this->orderDateTime = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $this->price(420.69);
        $this->shippingCosts(0);
        $this->add('itemRounding', json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR));
        $this->add('totalRounding', json_decode(json_encode(new CashRoundingConfig(2, 0.01, true), \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR));
    }

    public function price(float $amount): self
    {
        $this->price = new CartPrice(
            $amount,
            $amount,
            $amount,
        );

        return $this;
    }

    public function shippingCosts(float $amount): self
    {
        $this->shippingCosts = new CalculatedPrice(
            $amount,
            $amount,
        );

        return $this;
    }

    /**
     * @param array<mixed> $customParams
     */
    public function addTransaction(string $key, array $customParams = []): self
    {
        if (\array_key_exists('amount', $customParams)) {
            if (\is_float($customParams['amount'])) {
                $customParams['amount'] = new CalculatedPrice(
                    $customParams['amount'],
                    $customParams['amount'],
                );
            }
        }

        $transaction = \array_replace([
            'id' => $this->ids->get($key),
            'orderId' => $this->id,
            'paymentMethodId' => $this->getValidPaymentMethodId(),
            'amount' => new CalculatedPrice(
                420.69,
                420.69,
            ),
            'stateId' => $this->getStateMachineState(
                OrderTransactionStates::STATE_MACHINE,
                OrderTransactionStates::STATE_OPEN
            ),
        ], $customParams);

        $this->transactions[$this->ids->get($key)] = $transaction;

        return $this;
    }

    public function orderCustomer(string $nickname, string $customerNumber): self
    {
        $this->orderCustomer = [
            'id' => $this->ids->get('orderCustomer'),
            'orderId' => $this->id,
            'customerId' => $this->ids->get($customerNumber),
            'versionId' => Defaults::LIVE_VERSION,
            'orderVersionId' => Defaults::LIVE_VERSION,
            'nickname' => $nickname,
            'email' => 'some@mail.de',
        ];

        return $this;
    }
}
