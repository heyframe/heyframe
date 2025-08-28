<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityIdTrait;

/**
 * @phpstan-type Module array{name: string, label: array<string, string>, parent: string, source: string|null, position: int}
 * @phpstan-type Cookie array{snippet_name: string, snippet_description?: string, cookie: string, value?: string, expiration?: int, entries?: list<array{snippet_name: string, snippet_description?: string, cookie: string, value?: string, expiration?: int}>}
 */
class AppEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;
}
