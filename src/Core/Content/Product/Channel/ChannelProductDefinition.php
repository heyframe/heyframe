<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel;

use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPriceField;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiCriteriaAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Since;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ObjectField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelDefinitionInterface;

#[Package('inventory')]
class ChannelProductDefinition extends ProductDefinition implements ChannelDefinitionInterface
{
    private const PRICE_BASELINE = ['taxId', 'unitId', 'referenceUnit', 'purchaseUnit'];

    public function getEntityClass(): string
    {
        return ChannelProductEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ChannelProductCollection::class;
    }

    public function processCriteria(Criteria $criteria, ChannelContext $context): void
    {
        if (!$this->hasAvailableFilter($criteria)) {
            $criteria->addFilter(
                new ProductAvailableFilter($context->getChannelId(), ProductVisibilityDefinition::VISIBILITY_LINK)
            );
        }

        if ($criteria->getNestingLevel() !== Criteria::ROOT_NESTING_LEVEL) {
            return;
        }

        if (empty($criteria->getFields())) {
            $criteria
                ->addAssociation('prices')
                ->addAssociation('unit')
                ->addAssociation('deliveryTime')
                ->addAssociation('cover.media')
                ->addAssociation('tax')
            ;
        }

        if ($criteria->hasAssociation('productReviews')) {
            $association = $criteria->getAssociation('productReviews');
            $activeReviewsFilter = new MultiFilter(MultiFilter::CONNECTION_OR, [new EqualsFilter('status', true)]);
            if ($customer = $context->getCustomer()) {
                $activeReviewsFilter->addQuery(new EqualsFilter('customerId', $customer->getId()));
            }

            $association->addFilter($activeReviewsFilter);
        }
    }

    protected function defineFields(): FieldCollection
    {
        $fields = parent::defineFields();

        $fields->add(
            (new JsonField('calculated_price', 'calculatedPrice'))->addFlags(new ApiAware(), new Runtime(\array_merge(self::PRICE_BASELINE, ['price', 'prices'])))
        );
        $fields->add(
            (new ListField('calculated_prices', 'calculatedPrices'))->addFlags(new ApiAware(), new Runtime(\array_merge(self::PRICE_BASELINE, ['prices'])))
        );
        $fields->add(
            (new IntField('calculated_max_purchase', 'calculatedMaxPurchase'))->addFlags(new ApiAware(), new Runtime(['maxPurchase']))
        );
        $fields->add(
            (new JsonField('calculated_cheapest_price', 'calculatedCheapestPrice'))->addFlags(new ApiAware(), new Runtime(\array_merge(self::PRICE_BASELINE, ['cheapestPrice'])))
        );
        $fields->add(
            (new BoolField('is_new', 'isNew'))->addFlags(new ApiAware(), new Runtime(['releaseDate']))
        );
        $fields->add(
            (new OneToOneAssociationField('seoCategory', 'seoCategory', 'id', CategoryDefinition::class))->addFlags(new ApiAware(), new Runtime())
        );
        $fields->add(
            (new CheapestPriceField('cheapest_price', 'cheapestPrice'))->addFlags(new WriteProtected(), new Inherited(), new ApiCriteriaAware())
        );
        $fields->add(
            (new ObjectField('cheapest_price_container', 'cheapestPriceContainer'))->addFlags(new Runtime())
        );
        $fields->add(
            (new ObjectField('sortedProperties', 'sortedProperties'))->addFlags(new Runtime(), new ApiAware())
        );

        $fields->add(
            (new ObjectField('measurements', 'measurements'))->addFlags(new Runtime(), new ApiAware(), new Since('6.7.1.0'))
        );

        return $fields;
    }

    private function hasAvailableFilter(Criteria $criteria): bool
    {
        foreach ($criteria->getFilters() as $filter) {
            if ($filter instanceof ProductAvailableFilter) {
                return true;
            }
        }

        return false;
    }
}
