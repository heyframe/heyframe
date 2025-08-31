<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart;

use HeyFrame\Core\Checkout\Cart\Error\Error;
use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\CartDataCollection;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItemCollection;
use HeyFrame\Core\Checkout\Cart\Price\Struct\CartPrice;
use HeyFrame\Core\Checkout\Cart\Transaction\Struct\TransactionCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\StateAwareTrait;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('checkout')]
class Cart extends Struct
{
    use StateAwareTrait;

    protected CartPrice $price;

    protected LineItemCollection $lineItems;

    protected ErrorCollection $errors;

    protected TransactionCollection $transactions;

    protected bool $modified = false;

    /**
     * This can be used to identify carts, that are used for different purposes.
     * Setting this will call different hook names for respective cart sources.
     * This may be used for multi-cart or subscription applications,
     * where the regular calculation process is not desired and separate calculation processes are used as an opt-in usage.
     */
    protected ?string $source = null;

    protected ?string $hash = null;

    protected string $errorHash = '';

    private ?CartDataCollection $data = null;

    /**
     * @var array<string>
     */
    private array $ruleIds = [];

    private ?CartBehavior $behavior = null;

    /**
     * @internal
     */
    public function __construct(protected string $token)
    {
        $this->lineItems = new LineItemCollection();
        $this->transactions = new TransactionCollection();
        $this->errors = new ErrorCollection();
        $this->price = new CartPrice(0, 0, 0);
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getLineItems(): LineItemCollection
    {
        return $this->lineItems;
    }

    public function setLineItems(LineItemCollection $lineItems): void
    {
        $this->lineItems = $lineItems;
    }

    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }

    public function setErrors(ErrorCollection $errors): void
    {
        $this->errors = $errors;
    }

    /**
     * @throws CartException
     */
    public function addLineItems(LineItemCollection $lineItems): void
    {
        foreach ($lineItems as $lineItem) {
            $this->add($lineItem);
        }
    }

    public function addErrors(Error ...$errors): void
    {
        foreach ($errors as $error) {
            $this->errors->add($error);
        }
    }

    public function getPrice(): CartPrice
    {
        return $this->price;
    }

    public function setPrice(CartPrice $price): void
    {
        $this->price = $price;
    }

    /**
     * @throws CartException
     */
    public function add(LineItem $lineItem): self
    {
        $this->lineItems->add($lineItem);

        return $this;
    }

    /**
     * @return LineItem|null
     */
    public function get(string $lineItemKey)
    {
        return $this->lineItems->get($lineItemKey);
    }

    public function has(string $lineItemKey): bool
    {
        return $this->lineItems->has($lineItemKey);
    }

    /**
     * @throws CartException
     */
    public function remove(string $key): void
    {
        $item = $this->get($key);

        if (!$item) {
            throw CartException::lineItemNotFound($key);
        }

        if (!$item->isRemovable()) {
            throw CartException::lineItemNotRemovable($key);
        }

        $this->lineItems->remove($key);
    }

    public function getTransactions(): TransactionCollection
    {
        return $this->transactions;
    }

    public function setTransactions(TransactionCollection $transactions): self
    {
        $this->transactions = $transactions;

        return $this;
    }

    public function getData(): CartDataCollection
    {
        if (!$this->data) {
            $this->data = new CartDataCollection();
        }

        return $this->data;
    }

    public function setData(?CartDataCollection $data): void
    {
        $this->data = $data;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function markModified(): void
    {
        $this->modified = true;
    }

    public function markUnmodified(): void
    {
        $this->modified = false;
    }

    public function getApiAlias(): string
    {
        return 'cart';
    }

    /**
     * @param array<string> $ruleIds
     */
    public function setRuleIds(array $ruleIds): void
    {
        $this->ruleIds = $ruleIds;
    }

    /**
     * @return array<string>
     */
    public function getRuleIds(): array
    {
        return $this->ruleIds;
    }

    /**
     * Will be available after the cart gets calculated
     * The `\HeyFrame\Core\Checkout\Cart\Processor::process` will set this
     */
    public function getBehavior(): ?CartBehavior
    {
        return $this->behavior;
    }

    /**
     * @internal These function is reserved for the `\HeyFrame\Core\Checkout\Cart\Processor::process`
     */
    public function setBehavior(?CartBehavior $behavior): void
    {
        $this->behavior = $behavior;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): void
    {
        $this->source = $source;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): void
    {
        $this->hash = $hash;
    }

    public function getErrorHash(): string
    {
        return $this->errorHash;
    }

    public function setErrorHash(string $errorHash): void
    {
        $this->errorHash = $errorHash;
    }
}
