/*
 * @sw-package inventory
 */

import template from './sw-product-category-form.html.twig';
import './sw-product-category-form.scss';

const { Context, Mixin } = HeyFrame;
const { EntityCollection, Criteria } = HeyFrame.Data;
const { mapPropertyErrors } = HeyFrame.Component.getComponentHelper();

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'systemConfigApiService',
        'feature',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },
    },

    data() {
        return {
            displayVisibilityDetail: false,
            multiSelectVisible: true,
            channel: null,
            defaultVisibility: 30,
        };
    },

    computed: {
        product() {
            return HeyFrame.Store.get('swProductDetail').product;
        },

        parentProduct() {
            return HeyFrame.Store.get('swProductDetail').parentProduct;
        },

        loading() {
            return HeyFrame.Store.get('swProductDetail').loading;
        },

        isChild() {
            return HeyFrame.Store.get('swProductDetail').isChild;
        },

        showModeSetting() {
            return HeyFrame.Store.get('swProductDetail').showModeSetting;
        },

        ...mapPropertyErrors('product', [
            'tags',
            'active',
        ]),

        hasSelectedVisibilities() {
            if (this.product && this.product.visibilities) {
                return this.product.visibilities.length > 0;
            }
            return false;
        },

        productVisibilityRepository() {
            return this.repositoryFactory.create(this.product.visibilities.entity);
        },

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.channel = new EntityCollection(
                '/channel',
                'channel',
                HeyFrame.Context.api,
                new Criteria(1, 25),
            );
        },

        displayAdvancedVisibility() {
            this.displayVisibilityDetail = true;
        },

        closeAdvancedVisibility() {
            this.displayVisibilityDetail = false;
        },

        visibilitiesRemoveInheritanceFunction(newValue) {
            newValue.forEach(({ productVersionId, channelId, channel, visibility }) => {
                const visibilities = this.productVisibilityRepository.create(Context.api);

                Object.assign(visibilities, {
                    productId: this.product.id,
                    productVersionId,
                    channelId,
                    channel,
                    visibility,
                });

                this.product.visibilities.push(visibilities);
            });

            this.$refs.productVisibilitiesInheritance.forceInheritanceRemove = true;

            return this.product.visibilities;
        },
    },
};
