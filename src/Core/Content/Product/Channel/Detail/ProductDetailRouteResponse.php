<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Detail;

use HeyFrame\Core\Content\Product\Channel\ChannelProductEntity;
use HeyFrame\Core\Content\Property\PropertyGroupCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<ArrayStruct<array{product: ChannelProductEntity, configurator: PropertyGroupCollection|null}>>
 */
#[Package('inventory')]
class ProductDetailRouteResponse extends FrontApiResponse
{
    public function __construct(
        ChannelProductEntity $product,
        ?PropertyGroupCollection $configurator,
    ) {
        parent::__construct(new ArrayStruct([
            'product' => $product,
            'configurator' => $configurator,
        ], 'product_detail'));
    }

    public function getResult(): ArrayStruct
    {
        return $this->object;
    }

    public function getProduct(): ChannelProductEntity
    {
        return $this->object->get('product');
    }

    public function getConfigurator(): ?PropertyGroupCollection
    {
        return $this->object->get('configurator');
    }
}
