<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\MailTemplate;

use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateMedia\MailTemplateMediaDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateTranslation\MailTemplateTranslationDefinition;
use HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateType\MailTemplateTypeDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;

#[Package('after-sales')]
class MailTemplateDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'mail_template';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return MailTemplateEntity::class;
    }

    public function getCollectionClass(): string
    {
        return MailTemplateCollection::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        $fields = new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            (new FkField('mail_template_type_id', 'mailTemplateTypeId', MailTemplateTypeDefinition::class))->addFlags(new Required()),
            (new BoolField('system_default', 'systemDefault'))->addFlags(new ApiAware()),

            // translatable fields
            (new TranslatedField('senderName'))->addFlags(new ApiAware()),
            (new TranslatedField('description'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('subject'))->addFlags(new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('contentHtml'))->addFlags(new ApiAware()),
            (new TranslatedField('contentPlain'))->addFlags(new ApiAware()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(MailTemplateTranslationDefinition::class, 'mail_template_id'))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('mailTemplateType', 'mail_template_type_id', MailTemplateTypeDefinition::class, 'id'))
                ->addFlags(new ApiAware(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new OneToManyAssociationField('media', MailTemplateMediaDefinition::class, 'mail_template_id', 'id'))->addFlags(new ApiAware(), new CascadeDelete()),
        ]);

        return $fields;
    }
}
