<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateTypeTranslation;

use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use HeyFrame\Core\Framework\DataAbstractionLayer\TranslationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
class MailTemplateTypeTranslationEntity extends TranslationEntity
{
    use EntityCustomFieldsTrait;

    protected string $mailTemplateTypeId;

    protected ?MailTemplateTypeEntity $mailTemplateType = null;

    protected ?string $name = null;

    public function getMailTemplateTypeId(): string
    {
        return $this->mailTemplateTypeId;
    }

    public function setMailTemplateTypeId(string $mailTemplateTypeId): void
    {
        $this->mailTemplateTypeId = $mailTemplateTypeId;
    }

    public function getMailTemplateType(): ?MailTemplateTypeEntity
    {
        return $this->mailTemplateType;
    }

    public function setMailTemplateType(?MailTemplateTypeEntity $mailTemplateType): void
    {
        $this->mailTemplateType = $mailTemplateType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
