<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @internal
 *
 * @extends FrontApiResponse<Struct>
 */
#[Package('framework')]
class GenericFrontApiResponse extends FrontApiResponse
{
    public function __construct(
        int $code,
        Struct $object,
    ) {
        $this->setStatusCode($code);

        parent::__construct($object);
    }
}
