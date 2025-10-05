import template from './sw-price-field.html.twig';
import './sw-price-field.scss';

const { Application } = HeyFrame;
const { debounce } = HeyFrame.Utils;

/**
 * @sw-package framework
 *
 * @private
 * @status ready
 * @example-type static
 * @component-example
 * <sw-price-field"
 *                 :value="[{ gross: 11.90, currencyId: '...' }, ...]"
 *                 :defaultPrice="{...}"
 *                 :currency="{...}">
 * </sw-price-field>
 */
export default {
    template,
    inheritAttrs: false,

    inject: ['feature'],

    emits: [
        'change',
        'price-lock-change',
        'price-calculate',
        'price-gross-change',
        'price-net-change',
        'calculating',
    ],

    props: {
        value: {
            type: Array,
            required: true,
        },

        allowModal: {
            type: Boolean,
            default: false,
        },

        defaultPrice: {
            type: Object,
            required: false,
            default() {
                return {};
            },
        },

        hideListPrices: {
            type: Boolean,
            default: false,
        },

        currency: {
            type: Object,
            required: true,
            default() {
                return {};
            },
        },

        // eslint-disable-next-line vue/require-prop-types
        validation: {
            required: false,
            default: null,
        },

        // eslint-disable-next-line vue/require-prop-types
        label: {
            required: false,
            default: true,
        },

        // eslint-disable-next-line vue/require-prop-types
        compact: {
            required: false,
            default: false,
        },

        error: {
            type: Object,
            required: false,
            default: null,
        },

        // eslint-disable-next-line vue/require-prop-types
        disabled: {
            required: false,
            default: false,
        },

        disableSuffix: {
            type: Boolean,
            required: false,
            default: false,
        },

        grossLabel: {
            type: String,
            required: false,
            default: null,
        },

        netLabel: {
            type: String,
            required: false,
            default: null,
        },

        name: {
            type: String,
            required: false,
            default: null,
        },

        allowEmpty: {
            type: Boolean,
            required: false,
            default: false,
        },

        inherited: {
            type: Boolean,
            required: false,
            // eslint-disable-next-line vue/no-boolean-default
            default: undefined,
        },

        grossHelpText: {
            type: String,
            required: false,
            default: null,
        },
    },

    data() {
        return {
            showModal: false,
        };
    },

    computed: {
        calculatePriceApiService() {
            return Application.getContainer('factory').apiService.getByName('calculate-price');
        },

        priceForCurrency: {
            get() {
                const priceForCurrency = Object.values(this.value).find((price) => {
                    return price.currencyId === this.currency?.id;
                });

                // check if price exists
                if (priceForCurrency) {
                    return priceForCurrency;
                }

                // Calculate values if inherited
                if (this.isInherited) {
                    return {
                        currencyId: this.currency.id,
                        gross: Number.isNaN(this.defaultPrice.gross) ? null : this.convertPrice(this.defaultPrice.gross),
                        linked: this.defaultPrice.linked,
                        net: Number.isNaN(this.defaultPrice.net) ? null : this.convertPrice(this.defaultPrice.net),
                    };
                }

                return {
                    currencyId: this.currency.id,
                    gross: null,
                };
            },
            set(newValue) {
                this.priceForCurrency.gross = newValue.gross;
                this.priceForCurrency.linked = newValue.linked;
                this.priceForCurrency.net = newValue.net;
            },
        },

        attributesWithoutListeners() {
            const attributes = {};

            // Filter all listeners from the $attrs object
            Object.keys(this.$attrs).forEach((key) => {
                if (!key.startsWith('on')) {
                    attributes[key] = this.$attrs[key];
                }
            });

            return attributes;
        },

        isInherited() {
            if (this.inherited !== undefined) {
                return this.inherited;
            }

            const priceForCurrency = Object.values(this.value).find((price) => {
                return price.currencyId === this.currency.id;
            });

            return !priceForCurrency;
        },

        isDisabled() {
            return this.isInherited || this.disabled;
        },

        labelGross() {
            const label = this.grossLabel ? this.grossLabel : this.$tc('global.sw-price-field.labelPriceGross');
            return this.label ? label : '';
        },

        grossError() {
            return this.error ? this.error.gross : null;
        },

        grossFieldName() {
            return this.name ? `${this.name}-gross` : 'sw-price-field-gross';
        },
    },

    methods: {
        convertPrice(value) {
            return value * this.currency.factor;
        },

        keymonitor(event) {
            if (event.key === ',') {
                const value = event.target.value;
                event.target.value = value.replace(/,/, '.');
            }
        },

        onCloseModal() {
            this.showModal = false;
        },
    },
};
