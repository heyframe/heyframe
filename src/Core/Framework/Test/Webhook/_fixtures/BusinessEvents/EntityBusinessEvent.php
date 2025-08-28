<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\Webhook\_fixtures\BusinessEvents;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\EventData\EntityType;
use HeyFrame\Core\Framework\Event\EventData\EventDataCollection;
use HeyFrame\Core\Framework\Event\FlowEventAware;
use HeyFrame\Core\System\Tax\TaxDefinition;
use HeyFrame\Core\System\Tax\TaxEntity;

/**
 * @internal
 */
class EntityBusinessEvent implements FlowEventAware, BusinessEventEncoderTestInterface
{
    public function __construct(private readonly TaxEntity $tax)
    {
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('tax', new EntityType(TaxDefinition::class));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getEncodeValues(string $heyframeVersion): array
    {
        return [
            'tax' => [
                'id' => $this->tax->getId(),
                '_uniqueIdentifier' => $this->tax->getId(),
                'versionId' => null,
                'name' => $this->tax->getName(),
                'taxRate' => $this->tax->getTaxRate(),
                'position' => $this->tax->getPosition(),
                'customFields' => null,
                'translated' => [],
                'createdAt' => $this->tax->getCreatedAt() ? $this->tax->getCreatedAt()->format(\DATE_RFC3339_EXTENDED) : null,
                'updatedAt' => null,
                'extensions' => [],
                'apiAlias' => 'tax',
            ],
        ];
    }

    public function getName(): string
    {
        return 'test';
    }

    public function getContext(): Context
    {
        return Context::createDefaultContext();
    }

    public function getTax(): TaxEntity
    {
        return $this->tax;
    }
}
