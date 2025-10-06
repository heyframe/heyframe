<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\Mail\Transport;

use HeyFrame\Core\Checkout\Document\DocumentCollection;
use HeyFrame\Core\Content\Mail\Service\Mail;
use HeyFrame\Core\Content\Mail\Service\MailAttachmentsBuilder;
use HeyFrame\Core\Content\Mail\Service\MailAttachmentsConfig;
use HeyFrame\Core\Content\Mail\Transport\MailerTransportDecorator;
use HeyFrame\Core\Content\MailTemplate\MailTemplateEntity;
use HeyFrame\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use HeyFrame\Core\Framework\Adapter\Filesystem\MemoryFilesystemAdapter;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email;

/**
 * @internal
 */
#[CoversClass(MailerTransportDecorator::class)]
class MailerTransportDecoratorTest extends TestCase
{
    private MockObject&TransportInterface $decorated;

    private MockObject&MailAttachmentsBuilder $attachmentsBuilder;

    private Filesystem $filesystem;

    /** @var MockObject&EntityRepository<DocumentCollection> */
    private MockObject&EntityRepository $documentRepository;

    private MailerTransportDecorator $decorator;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(TransportInterface::class);
        $this->attachmentsBuilder = $this->createMock(MailAttachmentsBuilder::class);
        $this->filesystem = new Filesystem(new MemoryFilesystemAdapter());
        $this->documentRepository = $this->createMock(EntityRepository::class);

        $this->decorator = new MailerTransportDecorator(
            $this->decorated,
            $this->attachmentsBuilder,
            $this->filesystem,
            $this->documentRepository
        );
    }

    public function testMailerTransportDecoratorDefault(): void
    {
        $mail = $this->createMock(Email::class);
        $envelope = $this->createMock(Envelope::class);

        $this->decorated->expects($this->once())->method('send')->with($mail, $envelope);

        $this->decorator->send($mail, $envelope);
    }

    public function testMailerTransportDecoratorWithUrlAttachments(): void
    {
        $mail = new Mail();
        $envelope = $this->createMock(Envelope::class);
        $mail->addAttachmentUrl('foo');
        $mail->addAttachmentUrl('bar');

        $this->filesystem->write('foo', 'foo');
        $this->filesystem->write('bar', 'bar');

        $this->decorated->expects($this->once())->method('send')->with($mail, $envelope);

        $this->decorator->send($mail, $envelope);
        $attachments = $mail->getAttachments();
        static::assertCount(2, $attachments);

        static::assertSame('foo', $attachments[0]->getBody());
        static::assertSame('bar', $attachments[1]->getBody());
    }

    public function testMailerTransportDecoratorWithBuildAttachments(): void
    {
        $mail = new Mail();
        $envelope = $this->createMock(Envelope::class);
        $mailAttachmentsConfig = new MailAttachmentsConfig(
            Context::createDefaultContext(),
            new MailTemplateEntity(),
            new MailSendSubscriberConfig(false, ['foo', 'bar']),
        );

        $mail->setMailAttachmentsConfig($mailAttachmentsConfig);

        $this->decorated->expects($this->once())->method('send')->with($mail, $envelope);

        $this->attachmentsBuilder
            ->expects($this->once())
            ->method('buildAttachments')
            ->with(
                $mailAttachmentsConfig->getContext(),
                $mailAttachmentsConfig->getMailTemplate(),
                $mailAttachmentsConfig->getExtension(),
            )
            ->willReturn([
                ['id' => 'foo', 'content' => 'foo', 'fileName' => 'bar', 'mimeType' => 'baz/asd'],
                ['id' => 'bar', 'content' => 'bar', 'fileName' => 'bar', 'mimeType' => 'baz/asd'],
            ]);

        $this->decorator->send($mail, $envelope);

        $attachments = $mail->getAttachments();
        static::assertCount(2, $attachments);

        static::assertSame('foo', $attachments[0]->getBody());
        static::assertSame('bar', $attachments[1]->getBody());

        static::assertSame([], $mailAttachmentsConfig->getExtension()->getDocumentIds());
    }
}
