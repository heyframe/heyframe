<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Validation;

use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\Validation\Error\ErrorCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class TranslationValidator extends AbstractManifestValidator
{
    public function validate(Manifest $manifest, ?Context $context): ErrorCollection
    {
        $errors = new ErrorCollection();
        $error = $manifest->getMetadata()->validateTranslations();

        if ($error !== null) {
            $errors->add($error);
        }

        return $errors;
    }
}
