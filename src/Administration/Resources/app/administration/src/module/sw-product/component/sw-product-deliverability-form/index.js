/*
 * @sw-package inventory
 */

import template from './sw-product-deliverability-form.html.twig';

const { Mixin } = HeyFrame;
const { mapPropertyErrors } = HeyFrame.Component.getComponentHelper();

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    mixins: [
        Mixin.getByName('placeholder'),
    ],

    props: {
        allowEdit: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: true,
        },
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

        showModeSetting() {
            return HeyFrame.Store.get('swProductDetail').showModeSetting;
        },

        ...mapPropertyErrors('product', [
            'stock',
            'deliveryTimeId',
            'isCloseout',
            'maxPurchase',
            'purchaseSteps',
            'minPurchase',
            'shippingFree',
            'restockTime',
        ]),
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            if (typeof this.product.stock === 'undefined') {
                this.product.stock = 0;
            }
        },
    },
};
