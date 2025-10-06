<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Mail\Service;

use HeyFrame\Core\Content\Mail\Service\MailAttachmentsBuilder;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateMedia\MailTemplateMediaCollection;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateMedia\MailTemplateMediaEntity;
use HeyFrame\Core\Content\MailTemplate\MailTemplateEntity;
use HeyFrame\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Content\Media\MediaService;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Uuid\Uuid;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(MailAttachmentsBuilder::class)]
class MailAttachmentsBuilderTest extends TestCase
{
    private MockObject&MediaService $mediaService;

    /** @var MockObject&EntityRepository<MediaCollection> */
    private MockObject&EntityRepository $mediaRepository;

    private MailAttachmentsBuilder $attachmentsBuilder;

    protected function setUp(): void
    {
        $this->mediaService = $this->createMock(MediaService::class);
        $this->mediaRepository = $this->createMock(EntityRepository::class);

        $this->attachmentsBuilder = new MailAttachmentsBuilder(
            $this->mediaService,
            $this->mediaRepository,
        );
    }

    public function testBuildTemplateMediaAttachments(): void
    {
        $context = Context::createDefaultContext();
        $mailTemplate = new MailTemplateEntity();
        $extension = new MailSendSubscriberConfig(false);

        $mediaA = new MailTemplateMediaEntity();
        $mediaA->setId(Uuid::randomHex());
        $mediaA->setMedia(new MediaEntity());
        $mediaA->setLanguageId($context->getLanguageId());
        $mediaB = new MailTemplateMediaEntity();
        $mediaB->setId(Uuid::randomHex());
        $mediaC = new MailTemplateMediaEntity();
        $mediaC->setId(Uuid::randomHex());
        $mediaC->setMedia(new MediaEntity());
        $mediaC->setLanguageId($context->getLanguageId());

        $mailTemplate->setMedia(new MailTemplateMediaCollection([$mediaA, $mediaB, $mediaC]));

        $this->mediaService
            ->expects($this->exactly(2))
            ->method('getAttachment')
            ->willReturnOnConsecutiveCalls(
                [
                    'content' => 'foo',
                    'fileName' => 'foo',
                    'mimeType' => 'foo',
                ],
                [
                    'content' => 'bar',
                    'fileName' => 'bar',
                    'mimeType' => 'bar',
                ]
            );

        $attachments = $this->attachmentsBuilder->buildAttachments($context, $mailTemplate, $extension, [], Uuid::randomHex());

        static::assertSame(
            [
                [
                    'content' => 'foo',
                    'fileName' => 'foo',
                    'mimeType' => 'foo',
                ],
                [
                    'content' => 'bar',
                    'fileName' => 'bar',
                    'mimeType' => 'bar',
                ],
            ],
            $attachments
        );
    }
}
