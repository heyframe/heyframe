<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Validation;

use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\Validation\Error\AppNameError;
use HeyFrame\Core\Framework\App\Validation\Error\ErrorCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppNameValidator extends AbstractManifestValidator
{
    public function validate(Manifest $manifest, ?Context $context): ErrorCollection
    {
        $errors = new ErrorCollection();

        $appName = strtolower(substr($manifest->getPath(), strrpos($manifest->getPath(), '/') + 1));

        if ($appName !== strtolower($manifest->getMetadata()->getName())) {
            $errors->add(new AppNameError($manifest->getMetadata()->getName()));
        }

        return $errors;
    }
}
