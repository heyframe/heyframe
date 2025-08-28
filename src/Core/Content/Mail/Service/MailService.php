<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Mail\Service;

use HeyFrame\Core\Content\MailTemplate\Service\Event\MailBeforeSentEvent;
use HeyFrame\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use HeyFrame\Core\Content\MailTemplate\Service\Event\MailErrorEvent;
use HeyFrame\Core\Content\MailTemplate\Service\Event\MailSentEvent;
use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Maintenance\Staging\Event\SetupStagingEvent;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Locale\LanguageLocaleCodeProvider;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

#[Package('after-sales')]
class MailService extends AbstractMailService
{
    /**
     * @internal
     *
     * @param EntityRepository<MediaCollection> $mediaRepository
     * @param EntityRepository<ChannelCollection> $channelRepository
     */
    public function __construct(
        private readonly DataValidator $dataValidator,
        private readonly StringTemplateRenderer $templateRenderer,
        private readonly AbstractMailFactory $mailFactory,
        private readonly AbstractMailSender $mailSender,
        private readonly EntityRepository $mediaRepository,
        private readonly EntityRepository $channelRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly LanguageLocaleCodeProvider $languageLocaleProvider,
    ) {
    }

    public function getDecorated(): AbstractMailService
    {
        throw new DecorationPatternException(self::class);
    }

    public function send(array $data, Context $context, array $templateData = []): ?Email
    {
        $beforeValidateEvent = new MailBeforeValidateEvent($data, $context, $templateData);
        $this->eventDispatcher->dispatch($beforeValidateEvent);
        if ($beforeValidateEvent->isPropagationStopped()) {
            return null;
        }

        $data = $beforeValidateEvent->getData();
        $templateData = $beforeValidateEvent->getTemplateData();

        $this->dataValidator->validate($data, $this->getValidationDefinition($context));

        $mail = $this->createMail($data, $templateData, $context);
        if ($mail === null) {
            return null;
        }

        if (trim($mail->getBody()->toString()) === '') {
            $this->mailError('Mail body is null', $context, $templateData);

            return null;
        }

        if (isset($data['attachments']) && \is_array($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                if (!$attachment instanceof DataPart) {
                    $this->mailError(
                        errorMessage: 'Invalid attachment to mail provided, skipping this attachment',
                        context: $context,
                        templateData: $templateData,
                        level: Level::Warning,
                    );

                    continue;
                }

                $mail->addPart($attachment);
            }
        }

        $beforeSentEvent = new MailBeforeSentEvent($data, $mail, $context, $templateData['eventName'] ?? null);
        $this->eventDispatcher->dispatch($beforeSentEvent);
        if ($beforeSentEvent->isPropagationStopped()) {
            return null;
        }

        try {
            $this->mailSender->send($mail);
        } catch (\Throwable $exception) {
            $this->mailError(
                errorMessage: \sprintf('Could not send mail with error message: %s', $exception->getMessage()),
                context: $context,
                templateData: $templateData,
                template: (string) $mail->getHtmlBody(),
                exception: $exception,
            );

            return null;
        }

        $this->eventDispatcher->dispatch(new MailSentEvent(
            $data['subject'],
            $data['recipients'],
            ['text/html' => $mail->getHtmlBody(), 'text/plain' => $mail->getTextBody()],
            $context,
            $templateData['eventName'] ?? null,
        ));

        return $mail;
    }

    private function getValidationDefinition(Context $context): DataValidationDefinition
    {
        $definition = new DataValidationDefinition('mail_service.send');

        $definition->add('recipients', new NotBlank(), new Type('array'));
        $definition->add('channelId', new EntityExists(entity: ChannelDefinition::ENTITY_NAME, context: $context));
        $definition->add('contentHtml', new NotBlank(), new Type('string'));
        $definition->add('contentPlain', new NotBlank(), new Type('string'));
        $definition->add('subject', new NotBlank(), new Type('string'));

        return $definition;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $templateData
     */
    private function createMail(array &$data, array $templateData, Context $context): ?Email
    {
        $testMode = $this->systemConfigService->getBool(SetupStagingEvent::CONFIG_FLAG) || !empty($data['testMode']);

        $channel = $this->getChannel($data, $templateData, $context);

        $templateData['channel'] = $channel;
        $templateData['channelId'] = $channel?->getId();

        $senderEmail = $this->getSender($data, $channel?->getId());
        if ($senderEmail === '') {
            $this->mailError(
                \sprintf(
                    'senderMail not configured for channel: %s. Please check system_config \'core.basicInformation.email\'',
                    (string) $channel?->getId(),
                ),
                $context,
                $templateData,
            );
        }

        if ($testMode) {
            $this->templateRenderer->enableTestMode();
            if (\is_array($templateData['order'] ?? []) && empty($templateData['order']['deepLinkCode'])) {
                $templateData['order']['deepLinkCode'] = 'home';
            }
        }
        $mailOptions = ['subject'];
        if (\is_string($data['senderName'])) {
            $mailOptions[] = 'senderName';
        }
        foreach ($mailOptions as $renderDataIndex) {
            try {
                $data[$renderDataIndex] = $this->templateRenderer->render($data[$renderDataIndex], $templateData, $context, false);
            } catch (\Throwable $e) {
                $this->mailError(
                    \sprintf(
                        'Could not render Mail-%s with error message: %s',
                        ucfirst($renderDataIndex),
                        $e->getMessage(),
                    ),
                    $context,
                    $templateData,
                    $data[$renderDataIndex],
                    $e,
                    Level::Warning,
                );

                return null;
            }
        }

        // Validated through data validator
        \assert(\is_string($data['contentHtml']));
        \assert(\is_string($data['contentPlain']));

        $contents = [];
        foreach ($this->buildContents($data, $channel) as $index => $template) {
            try {
                $contents[$index] = $this->templateRenderer->render($template, $templateData, $context, $index !== 'text/plain');
            } catch (\Throwable $e) {
                $this->mailError(
                    \sprintf('Could not render Mail-Content (%s) with error message: %s', $index, $e->getMessage()),
                    $context,
                    $templateData,
                    $template,
                    $e,
                    Level::Warning,
                );

                return null;
            }
        }

        if ($testMode) {
            $this->templateRenderer->disableTestMode();
        }

        $mail = $this->mailFactory->create(
            $data['subject'],
            [$senderEmail => $data['senderName']],
            $data['recipients'],
            $contents,
            $this->getMediaUrls($data, $context),
            $data,
            $data['binAttachments'] ?? null
        );

        $mail->getHeaders()->addTextHeader(
            'Content-Language',
            $this->languageLocaleProvider->getLocaleForLanguageId($context->getLanguageId())
        );

        if ($testMode) {
            $headers = $mail->getHeaders();
            $headers->addTextHeader('X-HeyFrame-Language-Id', $context->getLanguageId());

            if (!empty($templateData['eventName'])) {
                $headers->addTextHeader('X-HeyFrame-Event-Name', $templateData['eventName']);
            }
            if ($channel instanceof ChannelEntity) {
                $headers->addTextHeader('X-HeyFrame-Sales-Channel-Id', $channel->getId());
            }
        }

        return $mail;
    }

    /**
     * @param array<string, mixed> $templateData
     */
    private function mailError(
        string $errorMessage,
        Context $context,
        array $templateData,
        ?string $template = null,
        ?\Throwable $exception = null,
        Level $level = Level::Error
    ): void {
        $this->eventDispatcher->dispatch(
            new MailErrorEvent($context, $level, $exception, $errorMessage, $template, $templateData)
        );

        $this->logger->log($level, $errorMessage, array_merge([
            'template' => $template,
            'exception' => (string) $exception,
        ], $templateData));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getSender(array $data, ?string $channelId): string
    {
        $senderEmail = $data['senderMail'] ?? $data['senderEmail'] ?? null;
        if (\is_string($senderEmail) && trim($senderEmail) !== '') {
            return trim($senderEmail);
        }

        return trim(
            $this->systemConfigService->getString(
                'core.basicInformation.email',
                $channelId
            )
        ) ?: trim(
            $this->systemConfigService->getString(
                'core.mailerSettings.senderAddress',
                $channelId
            )
        );
    }

    /**
     * Attaches header and footer to given email bodies
     *
     * @param array{contentPlain: string, contentHtml: string} $data
     *
     * @return array{'text/plain': string, 'text/html': string} e.g. ['text/plain' => '{{foobar}}', 'text/html' => '<h1>{{foobar}}</h1>']
     */
    private function buildContents(array $data, ?ChannelEntity $channel): array
    {
        $mailHeaderFooter = $channel?->getMailHeaderFooter();
        if ($mailHeaderFooter === null) {
            return [
                'text/plain' => $data['contentPlain'],
                'text/html' => $data['contentHtml'],
            ];
        }

        $headerPlain = $mailHeaderFooter->getTranslation('headerPlain') ?? '';
        \assert(\is_string($headerPlain));
        $footerPlain = $mailHeaderFooter->getTranslation('footerPlain') ?? '';
        \assert(\is_string($footerPlain));
        $headerHtml = $mailHeaderFooter->getTranslation('headerHtml') ?? '';
        \assert(\is_string($headerHtml));
        $footerHtml = $mailHeaderFooter->getTranslation('footerHtml') ?? '';
        \assert(\is_string($footerHtml));

        return [
            'text/plain' => \sprintf('%s%s%s', $headerPlain, $data['contentPlain'], $footerPlain),
            'text/html' => \sprintf('%s%s%s', $headerHtml, $data['contentHtml'], $footerHtml),
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<string>
     */
    private function getMediaUrls(array $data, Context $context): array
    {
        if (empty($data['mediaIds'])) {
            return [];
        }
        $criteria = new Criteria($data['mediaIds']);
        $criteria->setTitle('mail-service::resolve-media-ids');
        $media = new MediaCollection();
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($criteria, &$media): void {
            $media = $this->mediaRepository->search($criteria, $context)->getEntities();
        });

        $urls = [];
        foreach ($media as $mediaItem) {
            $urls[] = $mediaItem->getPath();
        }

        return $urls;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $templateData
     */
    private function getChannel(array $data, array $templateData, Context $context): ?ChannelEntity
    {
        $channel = $templateData['channel'] ?? null;
        if ($channel instanceof ChannelEntity) {
            return $channel;
        }

        $channelId = $data['channelId'] ?? null;
        if (\is_string($channelId)) {
            $criteria = new Criteria([$channelId]);
            $criteria->setTitle('mail-service::resolve-sales-channel-domain');
            $criteria->addAssociation('mailHeaderFooter');
            $criteria->getAssociation('domains')
                ->addFilter(
                    new EqualsFilter('languageId', $context->getLanguageId())
                );

            // Should never be null, since we check in the validation that if a channelId is present it is valid...
            return $this->channelRepository->search(
                $criteria,
                $context
            )->getEntities()->first();
        }

        return null;
    }
}
