<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Cart;

use HeyFrame\Core\Checkout\Cart\Error\Error;
use HeyFrame\Core\Checkout\Cart\LineItem\LineItem;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class PromotionCartDeletedInformationError extends Error
{
    private const KEY = 'promotion-discount-deleted';

    protected string $name;

    protected readonly string $discountLineItemId;

    public function __construct(LineItem $discountLineItem)
    {
        $this->name = $discountLineItem->getLabel() ?? '';
        $this->discountLineItemId = $discountLineItem->getId();
        $this->message = \sprintf(
            'Promotion %s has been deleted',
            $this->name
        );
        parent::__construct($this->message);
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return [
            'name' => $this->name,
            'discountLineItemId' => $this->discountLineItemId,
        ];
    }

    public function getId(): string
    {
        return \sprintf('%s-%s', self::KEY, $this->discountLineItemId);
    }

    public function getLevel(): int
    {
        return self::LEVEL_NOTICE;
    }

    public function blockOrder(): bool
    {
        return false;
    }
}
