<?php
declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Cart;

use HeyFrame\Core\Checkout\Cart\Error\Error;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductNotFoundError extends Error
{
    public function __construct(protected string $id)
    {
        parent::__construct('The product %s could not be found');
    }

    public function getParameters(): array
    {
        return ['id' => $this->id];
    }

    public function getId(): string
    {
        return $this->getMessageKey() . $this->id;
    }

    public function getMessageKey(): string
    {
        return 'product-not-found';
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
