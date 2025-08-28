<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Attribute;

use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\EntityHydrator;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Entity
{
    /**
     * @var class-string
     */
    public string $class;

    /**
     * @param class-string<EntityCollection> $collectionClass
     * @param class-string<EntityHydrator> $hydratorClass
     *
     * @phpstan-ignore missingType.generics (At this point it is not really possible to determine the correct entity class)
     */
    public function __construct(
        public string $name,
        public ?string $parent = null,
        public ?string $since = null,
        public string $collectionClass = EntityCollection::class,
        public string $hydratorClass = EntityHydrator::class,
    ) {
    }
}
