<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Changelog;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
enum ChangelogKeyword: string
{
    case ADDED = 'Added';
    case REMOVED = 'Removed';
    case CHANGED = 'Changed';
    case DEPRECATED = 'Deprecated';
}
