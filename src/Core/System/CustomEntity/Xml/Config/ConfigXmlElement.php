<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomEntity\Xml\Config;

use HeyFrame\Core\Framework\App\Manifest\Xml\XmlElement;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
abstract class ConfigXmlElement extends XmlElement
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        unset($data['extensions']);

        return $data;
    }
}
