<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class InvalidFilterQueryException extends DataAbstractionLayerException
{
}
