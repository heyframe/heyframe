import template from './sw-order-new-customer-modal.html.twig';
import './sw-order-new-customer-modal.scss';

/**
 * @sw-package checkout
 */

const { Mixin } = HeyFrame;
const { Criteria } = HeyFrame.Data;
const { mapPageErrors } = HeyFrame.Component.getComponentHelper();
const { CUSTOMER } = HeyFrame.Constants;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'numberRangeService',
        'systemConfigApiService',
        'customerValidationService',
    ],

    emits: [
        'on-select-existing-customer',
        'close',
    ],

    mixins: [
        Mixin.getByName('notification'),
    ],

    data() {
        return {
            customer: null,
            isLoading: false,
            customerNumberPreview: '',
            defaultSalutationId: null,
        };
    },

    computed: {
        ...mapPageErrors({
            'sw.order.new.customer.detail': {
                customer: [
                    'firstName',
                    'lastName',
                    'email',
                    'channelId',
                    'customerNumber',
                    'groupId',
                ],
            },

            'sw.order.new.customer.address': {
                customer_address: [
                    'firstName',
                    'lastName',
                    'street',
                    'city',
                    'countryId',
                ],
            },
        }),

        customerRepository() {
            return this.repositoryFactory.create('customer');
        },

        addressRepository() {
            return this.repositoryFactory.create('customer_address');
        },

        shippingAddress() {
            if (this.isSameBilling) {
                return this.billingAddress;
            }

            return this.customer !== null ? this.customer.addresses.get(this.customer.defaultShippingAddressId) : null;
        },

        billingAddress() {
            return this.customer !== null ? this.customer.addresses.get(this.customer.defaultBillingAddressId) : null;
        },

        isSameBilling: {
            get() {
                if (this.customer === null) {
                    return true;
                }

                return this.customer.defaultBillingAddressId === this.customer.defaultShippingAddressId;
            },

            set(newValue) {
                if (newValue === this.isSameBilling) {
                    return;
                }

                if (newValue === true) {
                    this.customer.defaultShippingAddressId = this.customer.defaultBillingAddressId;

                    // remove all addresses but default billing...
                    if (this.customer.isNew()) {
                        this.customer.addresses = this.customer.addresses.filter((address) => {
                            return address.id === this.customer.defaultBillingAddressId;
                        });
                    }

                    return;
                }

                const shippingAddress = this.addressRepository.create();
                shippingAddress.salutationId = this.defaultSalutationId;

                this.customer.addresses.add(shippingAddress);
                this.customer.defaultShippingAddressId = shippingAddress.id;
            },
        },

        validCompanyField() {
            return this.customer?.accountType === CUSTOMER.ACCOUNT_TYPE_BUSINESS
                ? this.customer?.company?.trim().length
                : true;
        },

        languageRepository() {
            return this.repositoryFactory.create('language');
        },

        languageCriteria() {
            const criteria = new Criteria();
            criteria.setLimit(1);

            if (this.customer?.channelId) {
                criteria.addFilter(Criteria.equals('channelDefaultAssignments.id', this.customer.channelId));
            }

            return criteria;
        },

        languageId() {
            return this.loadLanguage(this.customer.channelId);
        },

        salutationRepository() {
            return this.repositoryFactory.create('salutation');
        },

        salutationCriteria() {
            const criteria = new Criteria(1, 1);

            criteria.addFilter(Criteria.equals('salutationKey', 'not_specified'));

            return criteria;
        },
    },

    watch: {
        'customer.channelId'(channelId) {
            this.systemConfigApiService.getValues('core.systemWideLoginRegistration').then((response) => {
                if (response['core.systemWideLoginRegistration.isCustomerBoundToChannel']) {
                    this.customer.boundChannelId = channelId;
                }
            });
        },

        'customer.accountType'(value) {
            if (value === CUSTOMER.ACCOUNT_TYPE_BUSINESS) {
                return;
            }

            HeyFrame.Store.get('error').removeApiError(`customer_address.${this.billingAddress?.id}.company`);
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            this.customer = this.customerRepository.create();

            this.defaultSalutationId = await this.getDefaultSalutationId();

            const billingAddress = this.addressRepository.create();
            billingAddress.salutationId = this.defaultSalutationId;

            this.customer.addresses.add(billingAddress);

            this.customer.defaultShippingAddressId = billingAddress.id;
            this.customer.defaultBillingAddressId = billingAddress.id;
            this.customer.accountType = CUSTOMER.ACCOUNT_TYPE_PRIVATE;
            this.customer.vatIds = [];
            this.customer.salutationId = this.defaultSalutationId;
        },

        async onSave() {
            let hasError = false;

            const res = await this.validateEmail();

            if (!res || !res.isValid) {
                hasError = true;
            }

            if (!this.validCompanyField) {
                this.createErrorMessageForCompanyField();
                hasError = true;
            }

            if (this.customer.accountType === CUSTOMER.ACCOUNT_TYPE_PRIVATE) {
                this.customer.vatIds = [];
            }

            if (hasError) {
                this.createNotificationError({
                    message: this.$tc('sw-customer.detail.messageSaveError'),
                });

                this.isLoading = false;
                return false;
            }

            let numberRangePromise = Promise.resolve();
            if (this.customerNumberPreview === this.customer.customerNumber) {
                numberRangePromise = this.numberRangeService
                    .reserve('customer', this.customer.channelId)
                    .then((response) => {
                        this.customerNumberPreview = response.number;
                        this.customer.customerNumber = response.number;
                    });
            }

            return numberRangePromise.then(() => {
                return this.saveCustomer();
            });
        },

        async saveCustomer() {
            const languageId = await this.languageId;

            const context = { ...HeyFrame.Context.api, ...{ languageId } };

            return this.customerRepository
                .save(this.customer, context)
                .then((response) => {
                    this.$emit('on-select-existing-customer', this.customer.id);
                    this.isLoading = false;

                    this.onClose();

                    return response;
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-customer.detail.messageSaveError'),
                    });
                    this.isLoading = false;
                });
        },

        onChangeChannel(channelId) {
            this.customer.channelId = channelId;
            this.numberRangeService.reserve('customer', channelId, true).then((response) => {
                this.customerNumberPreview = response.number;
                this.customer.customerNumber = response.number;
            });
        },

        onClose() {
            this.$emit('close');
        },

        createErrorMessageForCompanyField() {
            HeyFrame.Store.get('error').addApiError({
                expression: `customer_address.${this.billingAddress.id}.company`,
                error: new HeyFrame.Classes.HeyFrameError({
                    code: 'c1051bb4-d103-4f74-8988-acbcafc7fdc3',
                }),
            });
        },

        validateEmail() {
            const { id, email, boundChannelId } = this.customer;

            if (!email) {
                return Promise.resolve({ isValid: true });
            }

            return this.customerValidationService
                .checkCustomerEmail({
                    id,
                    email,
                    boundChannelId,
                })
                .then((emailIsValid) => {
                    return emailIsValid;
                })
                .catch((exception) => {
                    if (!exception) {
                        return;
                    }

                    HeyFrame.Store.get('error').addApiError({
                        expression: `customer.${this.customer.id}.email`,
                        error: exception?.response?.data?.errors[0],
                    });
                });
        },

        async loadLanguage(channelId) {
            const languageId = HeyFrame.Context.api.languageId;

            if (!channelId) {
                return languageId;
            }

            const res = await this.languageRepository.searchIds(this.languageCriteria);

            if (!res?.data) {
                return languageId;
            }

            return res.data[0];
        },

        async getDefaultSalutationId() {
            const res = await this.salutationRepository.searchIds(this.salutationCriteria);

            return res.data?.[0];
        },
    },
};
