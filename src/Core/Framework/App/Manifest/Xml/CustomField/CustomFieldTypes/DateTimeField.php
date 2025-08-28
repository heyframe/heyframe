<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes;

use HeyFrame\Core\System\CustomField\CustomFieldTypes;

/**
 * @internal only for use by the app-system
 */
class DateTimeField extends CustomFieldType
{
    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::DATETIME,
            'config' => [
                'type' => 'date',
                'componentName' => 'sw-field',
                'customFieldType' => 'date',
                'config' => [
                    'time_24hr' => true,
                ],
                'dateType' => 'datetime',
            ],
        ];
    }
}
