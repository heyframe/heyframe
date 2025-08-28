<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PaymentMethodEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;
}
