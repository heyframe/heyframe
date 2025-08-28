<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Attribute;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
enum OnDelete: string
{
    case CASCADE = 'CASCADE';
    case SET_NULL = 'SET NULL';
    case RESTRICT = 'RESTRICT';
    case NO_ACTION = 'NO ACTION';
}
