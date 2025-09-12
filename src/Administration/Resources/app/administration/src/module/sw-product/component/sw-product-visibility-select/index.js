/*
 * @sw-package inventory
 */

import template from './sw-product-visibility-select.html.twig';

const { EntityCollection, Criteria } = HeyFrame.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    emits: ['item-add'],

    props: {
        criteria: {
            type: Object,
            required: false,
            default(props) {
                const criteria = new Criteria(1, props.resultLimit);
                criteria.addSorting(Criteria.sort('name', 'ASC'));
                return criteria;
            },
        },
    },

    data() {
        return {
            defaultVisibility: 30,
        };
    },

    computed: {
        product() {
            return HeyFrame.Store.get('swProductDetail').product;
        },

        repository() {
            return this.repositoryFactory.create('channel');
        },

        associationRepository() {
            return this.repositoryFactory.create('product_visibility');
        },
    },

    methods: {
        isSelected(item) {
            return this.currentCollection.some((entity) => {
                return entity.channelId === item.id;
            });
        },

        addItem(item) {
            // Remove when already selected
            if (this.isSelected(item)) {
                const associationEntity = this.currentCollection.find((entity) => {
                    return entity.channelId === item.id;
                });
                this.remove(associationEntity);

                return;
            }

            // Create new entity
            const newChannelAssociation = this.associationRepository.create(this.entityCollection.context);
            newChannelAssociation.productId = this.product.id;
            newChannelAssociation.productVersionId = this.product.versionId;
            newChannelAssociation.channelId = item.id;
            newChannelAssociation.visibility = this.defaultVisibility;
            newChannelAssociation.channel = item;

            this.$emit('item-add', item);

            const changedCollection = EntityCollection.fromCollection(this.currentCollection);
            changedCollection.add(newChannelAssociation);

            this.emitChanges(changedCollection);
            this.onSelectExpanded();
        },
    },
};
