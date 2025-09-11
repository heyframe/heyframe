<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Service;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Snippet\Struct\TranslationConfig;

#[Package('discovery')]
abstract class AbstractTranslationConfigLoader
{
    abstract public function getDecorated(): AbstractTranslationConfigLoader;

    abstract public function load(): TranslationConfig;
}
