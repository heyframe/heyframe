<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Mail\Service;

use HeyFrame\Core\Content\MailTemplate\MailTemplateEntity;
use HeyFrame\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig;
use HeyFrame\Core\Content\Media\MediaCollection;
use HeyFrame\Core\Content\Media\MediaService;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @phpstan-type MailAttachments array<int, array{id?: string, content: string, fileName: string, mimeType: string|null}>
 */
#[Package('after-sales')]
class MailAttachmentsBuilder
{
    /**
     * @param EntityRepository<MediaCollection> $mediaRepository
     */
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly EntityRepository $mediaRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $eventConfig
     *
     * @return MailAttachments
     */
    public function buildAttachments(
        Context $context,
        MailTemplateEntity $mailTemplate,
        MailSendSubscriberConfig $extensions,
    ): array {
        $attachments = [];

        foreach ($mailTemplate->getMedia() ?? [] as $mailTemplateMedia) {
            if ($mailTemplateMedia->getMedia() === null || $mailTemplateMedia->getLanguageId() !== $context->getLanguageId()) {
                continue;
            }

            $attachments[] = $this->mediaService->getAttachment(
                $mailTemplateMedia->getMedia(),
                $context
            );
        }

        if (empty($extensions->getMediaIds())) {
            return $attachments;
        }

        $criteria = new Criteria($extensions->getMediaIds());
        $criteria->setTitle('send-mail::load-media');

        $entities = $this->mediaRepository->search($criteria, $context)->getEntities();
        foreach ($entities as $media) {
            $attachments[] = $this->mediaService->getAttachment($media, $context);
        }

        return $attachments;
    }
}
