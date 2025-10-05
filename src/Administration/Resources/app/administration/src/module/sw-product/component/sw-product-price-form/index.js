/*
 * @sw-package inventory
 */

import template from './sw-product-price-form.html.twig';
import './sw-product-price-form.scss';

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

    data() {
        return {
            displayMaintainCurrencies: false,
        };
    },

    computed: {
        isLoading() {
            return HeyFrame.Store.get('swProductDetail').isLoading;
        },

        defaultPrice() {
            return HeyFrame.Store.get('swProductDetail').defaultPrice;
        },

        defaultCurrency() {
            return HeyFrame.Store.get('swProductDetail').defaultCurrency;
        },
        showModeSetting() {
            return HeyFrame.Store.get('swProductDetail').showModeSetting;
        },

        product() {
            return HeyFrame.Store.get('swProductDetail').product;
        },

        parentProduct() {
            return HeyFrame.Store.get('swProductDetail').parentProduct;
        },

        currencies() {
            return HeyFrame.Store.get('swProductDetail').currencies;
        },

        ...mapPropertyErrors('product', [
            'price',
        ]),

        prices: {
            get() {
                const prices = {
                    price: [],
                };

                if (this.product && Array.isArray(this.product.price)) {
                    prices.price = [...this.product.price];
                }

                return prices;
            },

            set(newValue) {
                this.product.price = newValue?.price || null;
            },
        },

        parentPrices() {
            return {
                price: this.product.price || this.parentProduct.price,
            };
        },
    },

    methods: {
        removePriceInheritation(refPrice) {
            const defaultRefPrice = refPrice.price?.find((price) => price.currencyId === this.defaultCurrency.id);

            const prices = {
                price: [],
            };

            if (defaultRefPrice) {
                prices.price.push({
                    currencyId: defaultRefPrice.currencyId,
                    gross: defaultRefPrice.gross,
                    listPrice: defaultRefPrice.listPrice ? defaultRefPrice.listPrice : null,
                    regulationPrice: defaultRefPrice.regulationPrice ? defaultRefPrice.regulationPrice : null,
                });
            }

            return prices;
        },

        inheritationCheckFunction() {
            return !this.prices.price.length;
        },

        onMaintainCurrenciesClose(prices) {
            this.product.price = prices;

            this.displayMaintainCurrencies = false;
        },
        updatePrices(index) {
            this.product.price.splice(index, 1);
        },
    },
};
