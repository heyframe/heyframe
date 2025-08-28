<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed, use StoreException::pluginNotAZipFile instead
 */
#[Package('framework')]
class PluginNotAZipFileException extends HeyFrameHttpException
{
    public function __construct(string $mimeType)
    {
        parent::__construct(
            'Given file must be a zip file. Given: {{ mimeType }}',
            ['mimeType' => $mimeType]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__PLUGIN_NOT_A_ZIP_FILE';
    }
}
