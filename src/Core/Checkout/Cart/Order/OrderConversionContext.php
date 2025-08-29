<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Order;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class OrderConversionContext extends Struct
{
    protected bool $includeCustomer = true;

    protected bool $includeTransactions = true;

    protected bool $includePersistentData = true;

    protected bool $includeOrderNumber = true;

    public function shouldIncludeCustomer(): bool
    {
        return $this->includeCustomer;
    }

    public function setIncludeCustomer(bool $includeCustomer): OrderConversionContext
    {
        $this->includeCustomer = $includeCustomer;

        return $this;
    }

    public function shouldIncludeTransactions(): bool
    {
        return $this->includeTransactions;
    }

    public function setIncludeTransactions(bool $includeTransactions): OrderConversionContext
    {
        $this->includeTransactions = $includeTransactions;

        return $this;
    }

    public function shouldIncludePersistentData(): bool
    {
        return $this->includePersistentData;
    }

    public function setIncludePersistentData(bool $includePersistentData): OrderConversionContext
    {
        $this->includePersistentData = $includePersistentData;

        return $this;
    }

    public function shouldIncludeOrderNumber(): bool
    {
        return $this->includeOrderNumber;
    }

    public function setIncludeOrderNumber(bool $includeOrderNumber): OrderConversionContext
    {
        $this->includeOrderNumber = $includeOrderNumber;

        return $this;
    }

    /**
     * @param array<array-key, mixed> $options
     *
     * @return $this
     */
    public function assign(array $options)
    {
        /** @deprecated tag:v6.8.0 - remove overwrite of assign function */
        if (isset($options['includeOrderDate'])) {
            $options['includePersistentData'] = $options['includeOrderDate'];
        } elseif (isset($options['includePersistentData'])) {
            $options['includeOrderDate'] = $options['includePersistentData'];
        }

        return parent::assign($options);
    }

    public function getApiAlias(): string
    {
        return 'cart_order_conversion_context';
    }
}
