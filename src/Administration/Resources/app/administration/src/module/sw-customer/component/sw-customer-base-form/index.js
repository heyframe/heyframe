import template from './sw-customer-base-form.html.twig';
import './sw-customer-base-form.scss';
import errorConfig from '../../error-config.json';

/**
 * @sw-package checkout
 */

const { Defaults } = HeyFrame;
const { mapPropertyErrors } = HeyFrame.Component.getComponentHelper();
const { Criteria } = HeyFrame.Data;
const { CUSTOMER } = HeyFrame.Constants;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['feature'],

    emits: ['channel-change'],

    props: {
        customer: {
            type: Object,
            required: true,
        },
    },

    computed: {
        ...mapPropertyErrors('customer', errorConfig['sw.customer.detail.base'].customer),

        accountTypeOptions() {
            return [
                {
                    value: CUSTOMER.ACCOUNT_TYPE_PRIVATE,
                    label: this.$tc('sw-customer.customerType.labelPrivate'),
                },
                {
                    value: CUSTOMER.ACCOUNT_TYPE_BUSINESS,
                    label: this.$tc('sw-customer.customerType.labelBusiness'),
                },
            ];
        },

        isBusinessAccountType() {
            return this.customer?.accountType === CUSTOMER.ACCOUNT_TYPE_BUSINESS;
        },
    },

    watch: {
        'customer.guest'(newVal) {
            if (newVal) {
                this.customer.password = null;
            }
        },
    },

    methods: {
        onChannelChange(channelId) {
            this.$emit('channel-change', channelId);
        },
    },
};
