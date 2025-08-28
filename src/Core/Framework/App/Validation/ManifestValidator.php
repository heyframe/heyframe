<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Validation;

use HeyFrame\Core\Framework\App\Exception\AppValidationException;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\Validation\Error\ErrorCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class ManifestValidator
{
    /**
     * @param iterable<AbstractManifestValidator> $validators
     */
    public function __construct(private readonly iterable $validators)
    {
    }

    public function validate(Manifest $manifest, Context $context): void
    {
        $errors = new ErrorCollection();
        foreach ($this->validators as $validator) {
            $errors->addErrors($validator->validate($manifest, $context));
        }

        if ($errors->count() === 0) {
            return;
        }

        throw new AppValidationException($manifest->getMetadata()->getName(), $errors);
    }
}
