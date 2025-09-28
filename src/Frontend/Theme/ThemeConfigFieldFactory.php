<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Theme\Exception\InvalidThemeConfigException;

#[Package('framework')]
class ThemeConfigFieldFactory
{
    public function create(string $name, array $configFieldArray): ThemeConfigField
    {
        $configField = new ThemeConfigField();
        $configField->setName($name);

        foreach ($configFieldArray as $key => $value) {
            $setter = 'set' . $key;
            if (!method_exists($configField, $setter)) {
                throw new InvalidThemeConfigException($key);
            }
            $configField->$setter($value); /* @phpstan-ignore-line */
        }

        return $configField;
    }
}
