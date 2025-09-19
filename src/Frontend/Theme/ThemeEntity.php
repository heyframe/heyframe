<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ThemeEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;
}
