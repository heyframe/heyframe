<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Validation\Error;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class AppNameError extends Error
{
    private const KEY = 'invalid-app-name';

    public function __construct(string $appName)
    {
        $this->message = \sprintf(
            'The technical app name "%s" in the "manifest.xml" and the folder name must be equal.',
            $appName
        );

        parent::__construct($this->message);
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }
}
