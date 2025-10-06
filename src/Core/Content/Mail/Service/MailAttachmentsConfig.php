<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Mail\Service;

use HeyFrame\Core\Content\MailTemplate\MailTemplateEntity;
use HeyFrame\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('after-sales')]
class MailAttachmentsConfig
{
    public function __construct(
        private Context $context,
        private MailTemplateEntity $mailTemplate,
        private MailSendSubscriberConfig $extension
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function setContext(Context $context): void
    {
        $this->context = $context;
    }

    public function getMailTemplate(): MailTemplateEntity
    {
        return $this->mailTemplate;
    }

    public function setMailTemplate(MailTemplateEntity $mailTemplate): void
    {
        $this->mailTemplate = $mailTemplate;
    }

    public function getExtension(): MailSendSubscriberConfig
    {
        return $this->extension;
    }

    public function setExtension(MailSendSubscriberConfig $extension): void
    {
        $this->extension = $extension;
    }
}
