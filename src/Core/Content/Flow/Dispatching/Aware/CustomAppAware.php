<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Aware;

use HeyFrame\Core\Framework\Event\IsFlowEventAware;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
#[IsFlowEventAware]
interface CustomAppAware
{
    public const CUSTOM_DATA = 'customAppData';

    /**
     * @return array<string, mixed>|null
     */
    public function getCustomAppData(): ?array;
}
