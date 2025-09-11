import template from './sw-settings-payment-sorting-modal.html.twig';
import './sw-settings-payment-sorting-modal.scss';

const { Mixin } = HeyFrame;

/**
 * @sw-package checkout
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
        'repositoryFactory',
        'feature',
    ],

    emits: [
        'modal-close',
        'modal-save',
    ],

    mixins: [Mixin.getByName('notification')],

    props: {
        paymentMethods: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            isSaving: false,
            originalPaymentMethods: [...this.paymentMethods],
            sortedPaymentMethods: [...this.paymentMethods],
            scrollOnDragConf: {
                speed: 50,
                margin: 130,
                accelerationMargin: -10,
            },
        };
    },

    computed: {
        paymentMethodRepository() {
            return this.repositoryFactory.create('payment_method');
        },

        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },
    },

    methods: {
        closeModal() {
            this.$emit('modal-close');
        },

        applyChanges() {
            this.isSaving = true;

            this.sortedPaymentMethods.map((paymentMethod, index) => {
                paymentMethod.position = index + 1;
                return paymentMethod;
            });

            return this.paymentMethodRepository
                .saveAll(this.sortedPaymentMethods, HeyFrame.Context.api)
                .then(() => {
                    this.isSaving = false;
                    this.$emit('modal-close');
                    this.$emit('modal-save');

                    this.createNotificationSuccess({
                        message: this.$tc('sw-settings-payment.sorting-modal.saveSuccessful'),
                    });
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-settings-payment.sorting-modal.errorMessage'),
                    });
                });
        },

        onSort(sortedItems) {
            this.sortedPaymentMethods = sortedItems;
        },

        isHeyFrameDefaultPaymentMethod(paymentMethod) {
            const defaultPaymentMethods = [
                'HeyFrame\\Core\\Checkout\\Payment\\Cart\\PaymentHandler\\InvoicePayment',
                'HeyFrame\\Core\\Checkout\\Payment\\Cart\\PaymentHandler\\CashPayment',
                'HeyFrame\\Core\\Checkout\\Payment\\Cart\\PaymentHandler\\PrePayment',
            ];

            if (!this.feature.isActive('v6.8.0.0')) {
                defaultPaymentMethods.push('HeyFrame\\Core\\Checkout\\Payment\\Cart\\PaymentHandler\\DebitPayment');
            }

            return defaultPaymentMethods.includes(paymentMethod.handlerIdentifier);
        },
    },
};
