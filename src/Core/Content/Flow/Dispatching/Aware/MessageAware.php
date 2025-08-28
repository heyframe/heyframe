<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Dispatching\Aware;

use HeyFrame\Core\Framework\Event\IsFlowEventAware;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Mime\Email;

#[Package('after-sales')]
#[IsFlowEventAware]
interface MessageAware
{
    public const MESSAGE = 'message';

    public function getMessage(): Email;
}
