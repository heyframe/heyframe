<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Manifest\Xml\Administration;

use HeyFrame\Core\Framework\App\Manifest\Xml\XmlElement;
use HeyFrame\Core\Framework\App\Manifest\XmlParserUtils;

/**
 * @internal only for use by the app-system
 */
class MainModule extends XmlElement
{
    protected string $source;

    public function getSource(): string
    {
        return $this->source;
    }

    protected static function parse(\DOMElement $element): array
    {
        return XmlParserUtils::parseAttributes($element);
    }
}
