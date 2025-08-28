<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Webhook\Hookable;

use HeyFrame\Core\Framework\Log\Package;

/**
 * Marker interface that EntityDefinitions can implement to automatically be tagged as hookable.
 *
 * @internal only for use by the app-system
 */
#[Package('framework')]
interface HookableEntityInterface
{
}
