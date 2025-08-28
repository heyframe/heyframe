<?php
declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cart;

use HeyFrame\Core\Checkout\Cart\Error\Error;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductOutOfStockError extends Error
{
    protected string $name;

    public function __construct(
        protected string $id,
        string $name,
    ) {
        $this->message = \sprintf('The product %s is no longer available', $name);

        parent::__construct($this->message);
        $this->name = $name;
    }

    public function getParameters(): array
    {
        return ['name' => $this->name];
    }

    public function getId(): string
    {
        return $this->getMessageKey() . $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessageKey(): string
    {
        return 'product-out-of-stock';
    }

    public function getLevel(): int
    {
        return self::LEVEL_ERROR;
    }

    public function blockOrder(): bool
    {
        return true;
    }
}
