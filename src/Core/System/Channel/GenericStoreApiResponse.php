<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @internal
 *
 * @extends StoreApiResponse<Struct>
 */
#[Package('framework')]
class GenericStoreApiResponse extends StoreApiResponse
{
    public function __construct(
        int $code,
        Struct $object,
    ) {
        $this->setStatusCode($code);

        parent::__construct($object);
    }
}
