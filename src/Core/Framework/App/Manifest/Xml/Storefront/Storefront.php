<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest\Xml\Storefront;

use HeyFrame\Core\Framework\App\Manifest\Xml\XmlElement;
use Symfony\Component\Config\Util\XmlUtils;

/**
 * @internal only for use by the app-system
 */
class Storefront extends XmlElement
{
    protected int $templateLoadPriority = 0;

    public function getTemplateLoadPriority(): int
    {
        return $this->templateLoadPriority;
    }

    protected static function parse(\DOMElement $element): array
    {
        $values = [];

        foreach ($element->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            if ($node->tagName === 'template-load-priority') {
                $values['templateLoadPriority'] = XmlUtils::phpize($node->textContent);
            }
        }

        return $values;
    }
}
