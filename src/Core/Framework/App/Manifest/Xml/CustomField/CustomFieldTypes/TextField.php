<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\CustomField\CustomFieldTypes;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class TextField extends CustomFieldType
{
    protected const TRANSLATABLE_FIELDS = ['label', 'help-text', 'placeholder'];

    /**
     * @var array<string, string>
     */
    protected array $placeholder = [];

    /**
     * @return array<string, string>
     */
    public function getPlaceholder(): array
    {
        return $this->placeholder;
    }

    protected function toEntityArray(): array
    {
        return [
            'type' => CustomFieldTypes::TEXT,
            'config' => [
                'type' => 'text',
                'placeholder' => $this->placeholder,
                'componentName' => 'sw-field',
                'customFieldType' => 'text',
            ],
        ];
    }
}
