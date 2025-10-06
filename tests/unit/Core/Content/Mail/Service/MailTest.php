<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Mail\Service;

use HeyFrame\Core\Content\Mail\Service\Mail;
use HeyFrame\Core\Content\Mail\Service\MailAttachmentsConfig;
use HeyFrame\Core\Content\MailTemplate\MailTemplateEntity;
use HeyFrame\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Uuid\Uuid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Mail::class)]
class MailTest extends TestCase
{
    public function testMailInstance(): void
    {
        $mail = new Mail();
        $mail->addAttachmentUrl('foobar');

        static::assertSame(['foobar'], $mail->getAttachmentUrls());

        $attachmentsConfig = new MailAttachmentsConfig(
            Context::createDefaultContext(),
            new MailTemplateEntity(),
            new MailSendSubscriberConfig(false),
            [],
            Uuid::randomHex()
        );

        $mail->setMailAttachmentsConfig($attachmentsConfig);

        static::assertSame($attachmentsConfig, $mail->getMailAttachmentsConfig());
    }
}
