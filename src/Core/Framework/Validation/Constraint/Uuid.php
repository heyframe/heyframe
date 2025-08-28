<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Validation\Constraint;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraint;

#[Package('framework')]
class Uuid extends Constraint
{
    final public const INVALID_MESSAGE = 'The string "{{ string }}" is not a valid uuid.';
    final public const INVALID_TYPE_MESSAGE = 'This value should be of type string.';
}
