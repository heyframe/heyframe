<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Content\MailTemplate\Api;

use HeyFrame\Core\Content\Mail\Service\AbstractMailService;
use HeyFrame\Core\Content\Mail\Service\MailAttachmentsConfig;
use HeyFrame\Core\Content\MailTemplate\Api\MailActionController;
use HeyFrame\Core\Content\MailTemplate\MailTemplateException;
use HeyFrame\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('after-sales')]
#[CoversClass(MailActionController::class)]
class MailActionControllerTest extends TestCase
{
    private AbstractMailService&MockObject $mailService;

    private StringTemplateRenderer&MockObject $stringTemplateRenderer;

    protected function setUp(): void
    {
        $this->stringTemplateRenderer = $this->createMock(StringTemplateRenderer::class);
        $this->mailService = $this->createMock(AbstractMailService::class);
    }

    public function testSendSuccess(): void
    {
        $data = new RequestDataBag([
            'id' => 'random',
            'mailTemplateData' => [
                'order' => [
                    'id' => Uuid::randomHex(),
                ],
            ],
            'documentIds' => ['1'],
        ]);

        $this->mailService->expects($this->once())
            ->method('send')
            ->with(
                static::callback(function (array $data) {
                    static::assertArrayHasKey('attachmentsConfig', $data);
                    static::assertInstanceOf(MailAttachmentsConfig::class, $data['attachmentsConfig']);

                    return true;
                }),
                static::anything(),
                static::anything()
            );

        $mailActionController = new MailActionController(
            $this->mailService,
            $this->stringTemplateRenderer
        );

        $mailActionController->send($data, Context::createDefaultContext());
    }

    public function testBuild(): void
    {
        $templateData = [
            'order' => [
                'id' => Uuid::randomHex(),
            ],
        ];

        $data = new RequestDataBag([
            'mailTemplateType' => [
                'templateData' => $templateData,
            ],
            'mailTemplate' => [
                'contentHtml' => 'html',
            ],
        ]);

        $context = Context::createDefaultContext();

        $this->stringTemplateRenderer->expects($this->once())
            ->method('enableTestMode');
        $this->stringTemplateRenderer->expects($this->once())
            ->method('disableTestMode');
        $this->stringTemplateRenderer->expects($this->once())
            ->method('render')
            ->with('html', $templateData, $context)
            ->willReturn('rendered');

        $mailActionController = new MailActionController(
            $this->mailService,
            $this->stringTemplateRenderer
        );

        $response = $mailActionController->build($data, $context);
        static::assertSame('"rendered"', $response->getContent());
    }

    public function testBuildWithoutTemplateData(): void
    {
        $data = new RequestDataBag([
            'mailTemplate' => [
                'contentHtml' => 'html',
            ],
        ]);

        $context = Context::createDefaultContext();

        $this->stringTemplateRenderer->expects($this->once())
            ->method('enableTestMode');
        $this->stringTemplateRenderer->expects($this->once())
            ->method('disableTestMode');
        $this->stringTemplateRenderer->expects($this->once())
            ->method('render')
            ->with('html', [], $context)
            ->willReturn('rendered');

        $mailActionController = new MailActionController(
            $this->mailService,
            $this->stringTemplateRenderer
        );

        $response = $mailActionController->build($data, $context);
        static::assertSame('"rendered"', $response->getContent());
    }

    public function testBuildWithoutTemplateContentThrows(): void
    {
        $data = new RequestDataBag();

        $context = Context::createDefaultContext();

        $this->stringTemplateRenderer->expects($this->never())
            ->method('enableTestMode');
        $this->stringTemplateRenderer->expects($this->never())
            ->method('disableTestMode');
        $this->stringTemplateRenderer->expects($this->never())
            ->method('render');

        $mailActionController = new MailActionController(
            $this->mailService,
            $this->stringTemplateRenderer
        );

        $this->expectExceptionObject(MailTemplateException::invalidMailTemplateContent());
        $mailActionController->build($data, $context);
    }
}
