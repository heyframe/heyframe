<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TreeLevelField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TreePathField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;

#[Package('discovery')]
class NavigationDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'navigation';
    final public const TYPE_PAGE = 'page';
    final public const TYPE_LINK = 'link';

    final public const TYPE_FOLDER = 'folder';

    final public const LINK_TYPE_EXTERNAL = 'external';

    final public const LINK_TYPE_CATEGORY = 'category';

    final public const LINK_TYPE_PRODUCT = 'product';

    final public const LINK_TYPE_LANDING_PAGE = 'landing_page';

    public function getCollectionClass(): string
    {
        return NavigationCollection::class;
    }

    public function getEntityClass(): string
    {
        return NavigationEntity::class;
    }

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new VersionField())->addFlags(new ApiAware()),
            (new ParentFkField(self::class))->addFlags(new ApiAware()),
            (new ReferenceVersionField(self::class, 'parent_version_id'))->addFlags(new ApiAware(), new Required()),
            (new FkField('after_navigation_id', 'afterNavigationyId', self::class))->addFlags(new ApiAware()),
            (new TreeLevelField('level', 'level'))->addFlags(new ApiAware()),
            (new TreePathField('path', 'path'))->addFlags(new ApiAware()),
            (new ChildCountField())->addFlags(new ApiAware()),
            (new StringField('type', 'type'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('visible', 'visible'))->addFlags(new ApiAware()),
            (new BoolField('active', 'active'))->addFlags(new ApiAware()), (new TranslatedField('name'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('customFields'))->addFlags(new ApiAware()),
            (new TranslatedField('linkType'))->addFlags(new ApiAware()),
            (new TranslatedField('internalLink'))->addFlags(new ApiAware()),
            (new TranslatedField('externalLink'))->addFlags(new ApiAware()),
            (new TranslatedField('linkNewTab'))->addFlags(new ApiAware()),
            (new TranslatedField('description'))->addFlags(new ApiAware()),
            (new TranslatedField('metaTitle'))->addFlags(new ApiAware()),
            (new TranslatedField('metaDescription'))->addFlags(new ApiAware()),
            (new TranslatedField('keywords'))->addFlags(new ApiAware()),
            new OneToManyAssociationField('navigationChannels', ChannelDefinition::class, 'navigation_category_id'),
            new OneToManyAssociationField('footerChannels', ChannelDefinition::class, 'footer_category_id'),
            new OneToManyAssociationField('serviceChannels', ChannelDefinition::class, 'service_category_id'),
        ]);
    }
}
