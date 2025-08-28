<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;
use HeyFrame\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

#[Package('fundamentals@after-sales')]
class OrderSerializer extends EntitySerializer
{
    public function supports(string $entity): bool
    {
        return $entity === OrderDefinition::ENTITY_NAME;
    }

    public function serialize(Config $config, EntityDefinition $definition, $entity): iterable
    {
        if ($entity === null) {
            return;
        }

        if ($entity instanceof Struct) {
            $entity = $entity->jsonSerialize();
        }

        yield from parent::serialize($config, $definition, $entity);

        if (isset($entity['lineItems']) && $entity['lineItems'] instanceof OrderLineItemCollection) {
            $lineItems = $entity['lineItems']->getElements();
            $modifiedLineItems = [];

            foreach ($lineItems as $lineItem) {
                $lineItem = $lineItem->jsonSerialize();

                $modifiedLineItems[] = $lineItem['quantity'] . 'x ' . $lineItem['productId'];
            }

            $entity['lineItems'] = implode('|', $modifiedLineItems);
        }

        if (isset($entity['deliveries']) && $entity['deliveries'] instanceof OrderDeliveryCollection && $entity['deliveries']->count() > 0) {
            $entity['deliveries'] = $entity['deliveries']->first()?->jsonSerialize();

            if (!empty($entity['deliveries']['trackingCodes'])) {
                $entity['deliveries']['trackingCodes'] = implode('|', $entity['deliveries']['trackingCodes']);
            }
        }

        if (isset($entity['transactions']) && $entity['transactions'] instanceof OrderTransactionCollection && $entity['transactions']->count() > 0) {
            $entity['transactions'] = $entity['transactions']->first()?->jsonSerialize();

            if (!empty($entity['transactions']['stateMachineState']) && $entity['transactions']['stateMachineState'] instanceof StateMachineStateEntity) {
                $entity['transactions']['stateMachineState'] = $entity['transactions']['stateMachineState']->jsonSerialize();
            }
        }

        if (isset($entity['itemRounding']) && $entity['itemRounding'] instanceof CashRoundingConfig) {
            $entity['itemRounding'] = $entity['itemRounding']->jsonSerialize();
        }

        if (isset($entity['totalRounding']) && $entity['totalRounding'] instanceof CashRoundingConfig) {
            $entity['totalRounding'] = $entity['totalRounding']->jsonSerialize();
        }

        yield from $entity;
    }
}
