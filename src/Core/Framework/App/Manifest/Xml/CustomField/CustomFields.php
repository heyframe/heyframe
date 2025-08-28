<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest\Xml\CustomField;

use HeyFrame\Core\Framework\App\Manifest\Xml\XmlElement;

/**
 * @internal only for use by the app-system
 */
class CustomFields extends XmlElement
{
    /**
     * @var list<CustomFieldSet>
     */
    protected array $customFieldSets = [];

    /**
     * @return list<CustomFieldSet>
     */
    public function getCustomFieldSets(): array
    {
        return $this->customFieldSets;
    }

    protected static function parse(\DOMElement $element): array
    {
        $customFieldSets = [];
        foreach ($element->getElementsByTagName('custom-field-set') as $customFieldSet) {
            $customFieldSets[] = CustomFieldSet::fromXml($customFieldSet);
        }

        return ['customFieldSets' => $customFieldSets];
    }
}
