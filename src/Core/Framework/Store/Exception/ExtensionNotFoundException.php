<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\StoreException;

#[Package('checkout')]
class ExtensionNotFoundException extends StoreException
{
}
