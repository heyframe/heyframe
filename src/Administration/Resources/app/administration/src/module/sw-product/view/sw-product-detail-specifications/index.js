/*
 * @sw-package inventory
 */

import template from './sw-product-detail-specifications.html.twig';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
        'feature',
        'repositoryFactory',
    ],

    data() {
        return {
            showMediaModal: false,
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

        isLoading() {
            return HeyFrame.Store.get('swProductDetail').isLoading;
        },

        customFieldSets() {
            return HeyFrame.Store.get('swProductDetail').customFieldSets;
        },

        showModeSetting() {
            return HeyFrame.Store.get('swProductDetail').showModeSetting;
        },

        productStates() {
            return HeyFrame.Store.get('swProductDetail').productStates;
        },

        customFieldsExists() {
            return !this.customFieldSets.length <= 0;
        },

        showCustomFieldsCard() {
            return this.showProductCard('custom_fields') && !this.isLoading && this.customFieldsExists;
        },
    },

    methods: {
        showProductCard(key) {
            return HeyFrame.Store.get('swProductDetail').showProductCard(key);
        },
    },
};
