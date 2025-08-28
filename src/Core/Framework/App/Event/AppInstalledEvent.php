<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Event;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('framework')]
class AppInstalledEvent extends ManifestChangedEvent
{
    final public const NAME = 'app.installed';

    public function getName(): string
    {
        return self::NAME;
    }
}
