<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Mail\Service;

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
#[CoversClass(MailAttachmentsConfig::class)]
class MailAttachmentsConfigTest extends TestCase
{
    public function testMailAttachmentsConfigInstance(): void
    {
        $context = Context::createDefaultContext();
        $mailTemplate = new MailTemplateEntity();
        $extension = new MailSendSubscriberConfig(false);
        $evenConfig = [];
        $orderId = Uuid::randomHex();

        $attachmentsConfig = new MailAttachmentsConfig(
            $context,
            $mailTemplate,
            $extension,
            $evenConfig,
            $orderId
        );

        static::assertSame($context, $attachmentsConfig->getContext());
        static::assertSame($mailTemplate, $attachmentsConfig->getMailTemplate());
        static::assertSame($extension, $attachmentsConfig->getExtension());

        $attachmentsConfig = $this->getMockBuilder(MailAttachmentsConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $attachmentsConfig->setContext($context);
        $attachmentsConfig->setMailTemplate($mailTemplate);
        $attachmentsConfig->setExtension($extension);

        static::assertSame($context, $attachmentsConfig->getContext());
        static::assertSame($mailTemplate, $attachmentsConfig->getMailTemplate());
        static::assertSame($extension, $attachmentsConfig->getExtension());
    }
}
