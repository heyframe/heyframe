<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class InvalidCriteriaIdsException extends DataAbstractionLayerException
{
}
