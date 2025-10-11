# Entity

@README.md

## Source Code References

- `ContentRouteEntity` - Content route entity (used in both Admin and Store APIs)
- `ContentRouteDefinition` - Entity definition with field-level API visibility
- `ContentRouteChannelEntity` - Mapping entity (many-to-many junction table)
- `ContentRouteChannelDefinition` - Mapping definition (route ↔ sales channel)

## Constraints

### Sales Channel Assignment

**ContentRouteChannelEntity** is a **mapping entity** (junction table), not a route entity:

- Maps many-to-many relationship: ContentRoute ↔ Channel
- Created automatically by DAL when loading `salesChannels` association
- Never instantiated directly in application code
- Used by RouteCollectionBuilder to filter routes per sales channel

**Global vs. Channel-Specific Routes**:

```php
// Global route (visible in all channels)
$route->setChannels(null);  // or empty collection

// Channel-specific route (visible only in assigned channels)
$route->setChannels($salesChannelCollection);
```

RouteCollectionBuilder filters routes:
```php
if ($salesChannels === null || $salesChannels->count() === 0 || $salesChannels->has($salesChannelId))
```

Routes without assignments are global. Routes with assignments are visible only in those channels.

### Entity Structure

Routes must have:
- `url_pattern`: Pattern with `{placeholders}`
- `parameter_binding`: Maps placeholders to resolution rules
- `priority`: Tie-breaker for pattern matching

Layout assignments stored in `content_layout_assignment` table with `route_id` foreign key.

### Parameter Binding Structure

See Routing/IdResolution/AGENTS.md for detailed structure.

Basic format:
```php
[
    'paramName' => [
        'placeholder' => 'outputName',  // Optional, defaults to paramName
        'resolution' => [               // Optional, omit for passthrough
            'entity' => 'entity_name',
            'match_field' => 'field_name'
        ]
    ]
]
```

### Layout Assignment

Layout assignments stored in `content_layout_assignment` table, not on route entity.

Create assignments via `content_layout_assignment.repository`:
```php
$this->assignmentRepository->create([[
    'routeId' => $routeId,
    'entityType' => 'product',       // or null for route-level default
    'entityId' => $productId,        // or null for wildcard
    'associationPath' => null,       // or 'product.categories' for association matching
    'salesChannelId' => $scId,       // or null for global
    'layoutId' => $layoutId,
    'priority' => 100,               // evaluation order (DESC)
]], $context);
```

## Quick Reference

- **URL pattern**: Symfony route syntax with {parameters}
- **Priority**: Integer for tie-breaking
- **ID generation**: Always Uuid::randomHex()
