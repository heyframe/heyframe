<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
#[IsFlowEventAware]
interface LanguageAware
{
    public const LANGUAGE_ID = 'languageId';

    public function getLanguageId(): ?string;
}
