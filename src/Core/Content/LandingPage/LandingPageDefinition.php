<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\LandingPage;

use HeyFrame\Core\Content\Cms\CmsPageDefinition;
use HeyFrame\Core\Content\LandingPage\Aggregate\LandingPageChannel\LandingPageChannelDefinition;
use HeyFrame\Core\Content\LandingPage\Aggregate\LandingPageTag\LandingPageTagDefinition;
use HeyFrame\Core\Content\LandingPage\Aggregate\LandingPageTranslation\LandingPageTranslationDefinition;
use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\Tag\TagDefinition;

#[Package('discovery')]
class LandingPageDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'landing_page';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return LandingPageCollection::class;
    }

    public function getEntityClass(): string
    {
        return LandingPageEntity::class;
    }

    public function since(): ?string
    {
        return '6.4.0.0';
    }

    protected function defineFields(): FieldCollection
    {
        $collection = new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()),
            (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            (new TranslatedField('slotConfig'))->addFlags(new ApiAware()),
            (new TranslatedField('metaTitle'))->addFlags(new ApiAware()),
            (new TranslatedField('metaDescription'))->addFlags(new ApiAware()),
            (new TranslatedField('keywords'))->addFlags(new ApiAware()),
            (new TranslatedField('url'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(LandingPageTranslationDefinition::class, 'landing_page_id'))->addFlags(new ApiAware(), new Required()),
            (new ManyToManyAssociationField('tags', TagDefinition::class, LandingPageTagDefinition::class, 'landing_page_id', 'tag_id'))->addFlags(new CascadeDelete()),
            (new FkField('cms_page_id', 'cmsPageId', CmsPageDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField('channels', ChannelDefinition::class, LandingPageChannelDefinition::class, 'landing_page_id', 'channel_id'))->addFlags(new CascadeDelete()),
            (new OneToManyAssociationField('seoUrls', SeoUrlDefinition::class, 'foreign_key'))->addFlags(new ApiAware()),
        ]);

        $collection->add((new ReferenceVersionField(CmsPageDefinition::class))->addFlags(new Required(), new ApiAware()));

        return $collection;
    }
}
