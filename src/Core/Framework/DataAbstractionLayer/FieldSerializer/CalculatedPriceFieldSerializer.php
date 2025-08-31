<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer;

use HeyFrame\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\ListPrice;
use HeyFrame\Core\Checkout\Cart\Price\Struct\ReferencePrice;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class CalculatedPriceFieldSerializer extends JsonFieldSerializer
{
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        $value = json_decode(json_encode($data->getValue(), \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR);

        unset($value['extensions']);
        if (isset($value['listPrice'])) {
            unset($value['listPrice']['extensions']);
        }

        $data->setValue($value);

        yield from parent::encode($field, $existence, $data, $parameters);
    }

    public function decode(Field $field, mixed $value): ?CalculatedPrice
    {
        if ($value === null) {
            return null;
        }

        $decoded = parent::decode($field, $value);
        if (!\is_array($decoded)) {
            return null;
        }

        $referencePriceDefinition = null;
        if (isset($decoded['referencePrice'])) {
            $refPrice = $decoded['referencePrice'];

            $referencePriceDefinition = new ReferencePrice(
                $refPrice['price'],
                $refPrice['purchaseUnit'],
                $refPrice['referenceUnit'],
            );
        }

        $listPrice = null;
        if (isset($decoded['listPrice'])) {
            $listPrice = ListPrice::createFromUnitPrice(
                (float) $decoded['unitPrice'],
                (float) $decoded['listPrice']['price']
            );
        }

        return new CalculatedPrice(
            (float) $decoded['unitPrice'],
            (float) $decoded['totalPrice'],
            (int) $decoded['quantity'],
            $referencePriceDefinition,
            $listPrice
        );
    }
}
