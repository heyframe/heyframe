<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class AbstractDictLoader
{
    abstract public function getDecorated(): AbstractDictLoader;

    abstract public function load(?string $key, Context $context): DictCollection;
}
