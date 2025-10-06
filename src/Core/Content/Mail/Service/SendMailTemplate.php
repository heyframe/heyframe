<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Mail\Service;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Mail\MailException;
use HeyFrame\Core\Content\MailTemplate\MailTemplateCollection;
use HeyFrame\Core\Content\MailTemplate\MailTemplateEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Adapter\Translation\AbstractTranslator;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\System\Locale\LanguageLocaleCodeProvider;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for sending mail using user-defined mail templates.
 * If you don't need user-configurable mail templates consider using \Symfony\Component\Mailer\MailerInterface
 *
 * @see https://symfony.com/doc/current/mailer.html
 */
#[Package('after-sales')]
class SendMailTemplate
{
    /**
     * @param EntityRepository<MailTemplateCollection> $mailTemplateRepository
     *
     * @internal
     */
    public function __construct(
        private readonly AbstractMailService $emailService,
        private readonly EntityRepository $mailTemplateRepository,
        private readonly LoggerInterface $logger,
        private readonly AbstractTranslator $translator,
        private readonly LanguageLocaleCodeProvider $languageLocaleProvider,
        private readonly Connection $connection
    ) {
    }

    public function send(SendMailTemplateParams $params, Context $context): void
    {
        $languageContext = $this->buildContext($params->languageId, $context);

        $mailTemplate = $this->getMailTemplate($params->mailTemplateId, $languageContext);
        if ($mailTemplate === null) {
            throw MailException::mailTemplateNotFound($params->mailTemplateId);
        }

        $sender = $params->senderName ?? $mailTemplate->getTranslation('senderName');

        $recipients = [];
        foreach ($params->recipients as $recipient) {
            $recipients[$recipient->getAddress()] = $recipient->getName();
        }

        $bag = new DataBag();
        $bag->set('recipients', $recipients);
        $bag->set('senderName', $sender);
        $bag->set('languageId', $params->languageId);
        $bag->set('channelId', $params->channelId);
        $bag->set('templateId', $mailTemplate->getId());
        $bag->set('customFields', $mailTemplate->getCustomFields());
        $bag->set('contentHtml', $mailTemplate->getTranslation('contentHtml'));
        $bag->set('contentPlain', $mailTemplate->getTranslation('contentPlain'));
        $bag->set('subject', $mailTemplate->getTranslation('subject'));
        $bag->set('mediaIds', []);
        $bag->set('attachments', $params->attachments);

        $this->_send($bag, $languageContext, $params->data, $params->channelId);
    }

    /**
     * @param array<string, mixed> $templateData
     */
    private function _send(DataBag $data, Context $context, array $templateData, ?string $channelId): void
    {
        $injected = $this->injectTranslator($context, $channelId);

        try {
            $this->emailService->send($data->all(), $context, $templateData);
        } catch (\Exception $e) {
            $this->logger->error(
                "Could not send mail:\n"
                . $e->getMessage() . "\n"
                . 'Error Code:' . $e->getCode() . "\n"
                . "Template data: \n"
                . json_encode($data->all(), \JSON_THROW_ON_ERROR) . "\n"
            );
        }

        if ($injected) {
            $this->translator->resetInjection();
        }
    }

    private function getMailTemplate(string $id, Context $context): ?MailTemplateEntity
    {
        $criteria = new Criteria([$id]);
        $criteria->setTitle('send-mail::load-mail-template');
        $criteria->setLimit(1);

        return $this->mailTemplateRepository->search($criteria, $context)->first();
    }

    private function injectTranslator(Context $context, ?string $channelId): bool
    {
        if ($channelId === null) {
            return false;
        }

        if ($this->translator->getSnippetSetId() !== null) {
            return false;
        }

        $this->translator->injectSettings(
            $channelId,
            $context->getLanguageId(),
            $this->languageLocaleProvider->getLocaleForLanguageId($context->getLanguageId()),
            $context
        );

        return true;
    }

    private function buildContext(string $languageId, Context $context): Context
    {
        $parent = $this->connection->fetchOne(
            'SELECT LOWER(HEX(language.parent_id)) FROM language WHERE language.id = :languageId',
            ['languageId' => Uuid::fromHexToBytes($languageId)]
        );

        $chain = array_filter(array_unique([$languageId, $parent, Defaults::LANGUAGE_SYSTEM]));

        $clone = clone $context;
        $clone->assign(['languageIdChain' => $chain]);

        return $clone;
    }
}
