<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;

class CanNotDeletePluginManagedByComposerException extends HeyFrameHttpException
{
    public function __construct(string $reason)
    {
        parent::__construct(
            'Can not delete plugin. Please contact your system administrator. Error: {{ reason }}',
            ['reason' => $reason]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__STORE_CANNOT_DELETE_PLUGIN_MANAGED_BY_SHOPWARE';
    }
}
