<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Flow\Events;

use HeyFrame\Core\Content\Flow\Dispatching\StorableFlow;
use HeyFrame\Core\Content\MailTemplate\MailTemplateEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameEvent;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;

class FlowSendMailActionEvent implements HeyFrameEvent
{
    public function __construct(
        private readonly DataBag $dataBag,
        private readonly MailTemplateEntity $mailTemplate,
        private readonly StorableFlow $flow
    ) {
    }

    public function getContext(): Context
    {
        return $this->flow->getContext();
    }

    public function getDataBag(): DataBag
    {
        return $this->dataBag;
    }

    public function getMailTemplate(): MailTemplateEntity
    {
        return $this->mailTemplate;
    }

    public function getStorableFlow(): StorableFlow
    {
        return $this->flow;
    }
}
