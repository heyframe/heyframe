<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;

/**
 * @extends StoreApiResponse<ArrayStruct<array{success: bool}>>
 */
#[Package('framework')]
class SuccessResponse extends StoreApiResponse
{
    public function __construct()
    {
        parent::__construct(new ArrayStruct(['success' => true]));
    }
}
