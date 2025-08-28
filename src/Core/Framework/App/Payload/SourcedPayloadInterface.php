<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Payload;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
interface SourcedPayloadInterface extends \JsonSerializable
{
    public function setSource(Source $source): void;
}
