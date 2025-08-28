<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Stub\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityExistence;

/**
 * @internal
 */
class EmptyEntityExistence extends EntityExistence
{
    public function __construct()
    {
        parent::__construct('', [], true, false, false, []);
    }
}
