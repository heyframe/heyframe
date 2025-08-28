<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes;

use HeyFrame\Core\System\CustomField\CustomFieldTypes;

/**
 * @internal only for use by the app-system
 */
class PriceField extends CustomFieldType
{
    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::PRICE,
            'config' => [
                'type' => 'price',
                'componentName' => 'sw-price-field',
                'customFieldType' => 'price',
            ],
        ];
    }
}
