<?php declare(strict_types=1);

namespace HeyFrame\Core\Installer\Requirements;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Installer\Requirements\Struct\RequirementsCheckCollection;

/**
 * @internal
 */
#[Package('framework')]
interface RequirementsValidatorInterface
{
    public function validateRequirements(RequirementsCheckCollection $checks): RequirementsCheckCollection;
}
