<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class PluginRegionStruct extends Struct
{
    protected PluginCategoryCollection $categories;

    public function __construct(
        protected string $name,
        protected string $label,
        iterable $categories,
    ) {
        $this->categories = new PluginCategoryCollection($categories);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCategories(): PluginCategoryCollection
    {
        return $this->categories;
    }

    public function getApiAlias(): string
    {
        return 'store_plugin_region';
    }
}
