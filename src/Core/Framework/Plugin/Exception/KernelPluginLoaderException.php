<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed. Use \HeyFrame\Core\Framework\Plugin\PluginException::kernelPluginLoaderError instead
 */
class KernelPluginLoaderException extends HeyFrameHttpException
{
    public function __construct(
        string $plugin,
        string $reason
    ) {
        parent::__construct(
            'Failed to load plugin "{{ plugin }}". Reason: {{ reason }}',
            ['plugin' => $plugin, 'reason' => $reason]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__KERNEL_PLUGIN_LOADER_ERROR';
    }
}
