<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;

class PluginExtractionException extends HeyFrameHttpException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            'Plugin extraction failed. Error: {{ error }}',
            ['error' => $reason]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__PLUGIN_EXTRACTION_FAILED';
    }
}
