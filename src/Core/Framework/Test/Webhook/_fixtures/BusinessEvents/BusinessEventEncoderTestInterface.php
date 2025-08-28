<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\Webhook\_fixtures\BusinessEvents;

/**
 * @internal
 */
interface BusinessEventEncoderTestInterface
{
    public function getEncodeValues(string $heyframeVersion): array;
}
