<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Post\Aggregate\PostTranslation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class PostTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;
}
