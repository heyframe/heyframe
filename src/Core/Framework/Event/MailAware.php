<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Framework\Event\EventData\MailRecipientStruct;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
#[IsFlowEventAware]
interface MailAware
{
    public const MAIL_STRUCT = 'mailStruct';

    public const CHANNEL_ID = 'channelId';

    public const TIMEZONE = 'timezone';

    public function getMailStruct(): MailRecipientStruct;

    public function getChannelId(): ?string;
}
