<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media;

use HeyFrame\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaFolder\MediaFolderDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaTag\MediaTagDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaThumbnail\MediaThumbnailDefinition;
use HeyFrame\Core\Content\Media\Aggregate\MediaTranslation\MediaTranslationDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductMedia\ProductMediaDefinition;
use HeyFrame\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;
use HeyFrame\Core\Framework\App\Aggregate\AppPaymentMethod\AppPaymentMethodDefinition;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BlobField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Computed;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Tag\TagDefinition;
use HeyFrame\Core\System\User\UserDefinition;

#[Package('discovery')]
class MediaDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'media';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return MediaCollection::class;
    }

    public function getEntityClass(): string
    {
        return MediaEntity::class;
    }

    public function since(): ?string
    {
        return '6.0.0.0';
    }

    public function getHydratorClass(): string
    {
        return MediaHydrator::class;
    }

    protected function defineFields(): FieldCollection
    {
        $fields = new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            new FkField('user_id', 'userId', UserDefinition::class),
            new FkField('media_folder_id', 'mediaFolderId', MediaFolderDefinition::class),
            (new StringField('mime_type', 'mimeType'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::LOW_SEARCH_RANKING)),
            (new StringField('file_extension', 'fileExtension'))->addFlags(new ApiAware()),
            (new DateTimeField('uploaded_at', 'uploadedAt'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new LongTextField('file_name', 'fileName'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new IntField('file_size', 'fileSize'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new BlobField('media_type', 'mediaTypeRaw'))->removeFlag(ApiAware::class)->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),
            (new JsonField('meta_data', 'metaData'))->addFlags(new ApiAware(), new WriteProtected(Context::SYSTEM_SCOPE)),
            (new JsonField('media_type', 'mediaType'))->addFlags(new WriteProtected(), new Runtime()),
            (new JsonField('config', 'config'))->addFlags(new ApiAware()),
            (new TranslatedField('alt'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new TranslatedField('title'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new StringField('url', 'url'))->addFlags(new ApiAware(), new Runtime(['path', 'private', 'updatedAt'])),
            (new StringField('path', 'path'))->addFlags(new ApiAware()),
            (new BoolField('has_file', 'hasFile'))->addFlags(new ApiAware(), new Runtime()),
            (new BoolField('private', 'private'))->addFlags(new ApiAware()),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            (new BlobField('thumbnails_ro', 'thumbnailsRo'))->removeFlag(ApiAware::class)->addFlags(new Computed()),
            (new TranslationsAssociationField(MediaTranslationDefinition::class, 'media_id'))->addFlags(new ApiAware(), new Required()),
            (new ManyToManyAssociationField('tags', TagDefinition::class, MediaTagDefinition::class, 'media_id', 'tag_id'))->addFlags(new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new OneToManyAssociationField('thumbnails', MediaThumbnailDefinition::class, 'media_id'))->addFlags(new ApiAware(), new CascadeDelete()),
            // reverse side of the associations, not available in front-api
            new ManyToOneAssociationField('user', 'user_id', UserDefinition::class, 'id', false),
            (new OneToManyAssociationField('productMedia', ProductMediaDefinition::class, 'media_id', 'id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('avatarUsers', UserDefinition::class, 'avatar_id'))->addFlags(new SetNullOnDelete()),
            new ManyToOneAssociationField('mediaFolder', 'media_folder_id', MediaFolderDefinition::class, 'id', false),
            (new OneToManyAssociationField('propertyGroupOptions', PropertyGroupOptionDefinition::class, 'media_id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('paymentMethods', PaymentMethodDefinition::class, 'media_id', 'id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('productConfiguratorSettings', ProductConfiguratorSettingDefinition::class, 'media_id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('orderLineItems', OrderLineItemDefinition::class, 'cover_id'))->addFlags(new SetNullOnDelete()),
            (new OneToManyAssociationField('appPaymentMethods', AppPaymentMethodDefinition::class, 'original_media_id', 'id'))->addFlags(new SetNullOnDelete()),
            (new StringField('file_hash', 'fileHash'))->addFlags(new Computed()),
        ]);

        return $fields;
    }
}
