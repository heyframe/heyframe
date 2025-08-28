<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class NoPluginFoundInZipException extends HeyFrameHttpException
{
    public function __construct(string $archive)
    {
        parent::__construct(
            'No plugin was found in the zip archive: {{ archive }}',
            ['archive' => $archive]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__PLUGIN_NO_PLUGIN_FOUND_IN_ZIP';
    }
}
