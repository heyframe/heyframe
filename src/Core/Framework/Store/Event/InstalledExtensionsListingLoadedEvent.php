<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Struct\ExtensionCollection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
#[Package('checkout')]
class InstalledExtensionsListingLoadedEvent extends Event
{
    public function __construct(public ExtensionCollection $extensionCollection, public readonly Context $context)
    {
    }
}
