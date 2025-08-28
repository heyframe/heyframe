<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ActionButton\Response;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
abstract class ActionButtonResponse extends Struct
{
    public function __construct(protected string $actionType)
    {
    }
}
