<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Currency;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class CurrencyEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;
}
