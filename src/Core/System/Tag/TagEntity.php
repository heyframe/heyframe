<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Tag;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Order\OrderCollection;
use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@framework')]
class TagEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected ?ProductCollection $products = null;

    protected ?MediaCollection $media = null;

    protected ?CustomerCollection $customers = null;

    protected ?OrderCollection $orders = null;

    protected ?RuleCollection $rules = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }

    public function getMedia(): ?MediaCollection
    {
        return $this->media;
    }

    public function setMedia(MediaCollection $media): void
    {
        $this->media = $media;
    }

    public function getCustomers(): ?CustomerCollection
    {
        return $this->customers;
    }

    public function setCustomers(CustomerCollection $customers): void
    {
        $this->customers = $customers;
    }

    public function getOrders(): ?OrderCollection
    {
        return $this->orders;
    }

    public function setOrders(OrderCollection $orders): void
    {
        $this->orders = $orders;
    }

    public function getRules(): ?RuleCollection
    {
        return $this->rules;
    }

    public function setRules(RuleCollection $rules): void
    {
        $this->rules = $rules;
    }
}
