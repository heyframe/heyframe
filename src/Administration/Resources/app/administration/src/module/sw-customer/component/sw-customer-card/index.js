import template from './sw-customer-card.html.twig';
import './sw-customer-card.scss';
import errorConfig from '../../error-config.json';
import ApiService from '../../../../core/service/api.service';

/**
 * @sw-package checkout
 */

const { Mixin, Defaults } = HeyFrame;
const { mapPropertyErrors } = HeyFrame.Component.getComponentHelper();
const { Criteria } = HeyFrame.Data;
const { CUSTOMER } = HeyFrame.Constants;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
        'contextStoreService',
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('notification'),
        Mixin.getByName('salutation'),
    ],

    props: {
        customer: {
            type: Object,
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        editMode: {
            type: Boolean,
            required: false,
            default: false,
        },
        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            showImitateCustomerModal: false,
        };
    },

    computed: {
        hasActionSlot() {
            return !!this.$slots.actions?.[0];
        },

        hasAdditionalDataSlot() {
            return !!this.$slots['data-additional']?.[0];
        },

        hasSummarySlot() {
            return !!this.$slots.summary?.[0];
        },

        moduleColor() {
            if (!this.$route.meta.$module) {
                return '';
            }
            return this.$route.meta.$module.color;
        },

        fullName() {
            const name = {
                name: this.salutation(this.customer),
                company: this.customer.company,
            };

            return Object.values(name)
                .filter((item) => item !== null)
                .join(' - ')
                .trim();
        },

        salutationCriteria() {
            const criteria = new Criteria(1, 25);

            criteria.addFilter(
                Criteria.not('or', [
                    Criteria.equals('id', Defaults.defaultSalutationId),
                ]),
            );

            return criteria;
        },

        ...mapPropertyErrors('customer', [
            ...errorConfig['sw.customer.detail.base'].customer,
        ]),

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

        canUseCustomerImitation() {
            if (this.customer.guest) {
                return false;
            }

            if (!this.customer.active) {
                return false;
            }

            if (this.customer.boundChannel) {
                if (!this.customer.boundChannel.active) {
                    return false;
                }

                if (this.customer.boundChannel.typeId !== Defaults.storefrontChannelTypeId) {
                    return false;
                }

                if (!this.customer.boundChannel.domains?.length) {
                    return false;
                }
            }

            return this.acl.can('api_proxy_imitate-customer');
        },

        customerImitationWarning() {
            if (this.customer.guest) {
                return this.$tc('sw-customer.card.tooltipImitateCustomerGuest');
            }

            if (!this.customer.active) {
                return this.$tc('sw-customer.card.tooltipImitateCustomerInactive');
            }

            if (this.customer.boundChannel) {
                if (!this.customer.boundChannel.active) {
                    return this.$tc('sw-customer.card.tooltipImitateCustomerInactiveChannel');
                }

                if (this.customer.boundChannel.typeId !== Defaults.storefrontChannelTypeId) {
                    return this.$tc('sw-customer.card.tooltipImitateCustomerNoStorefront');
                }

                if (!this.customer.boundChannel.domains?.length) {
                    return this.$tc('sw-customer.card.tooltipImitateCustomerNoDomain');
                }
            }

            return this.$tc('sw-privileges.tooltip.warning');
        },

        hasSingleBoundChannelUrl() {
            return this.customer.boundChannel?.domains?.length === 1;
        },

        currentUser() {
            return HeyFrame.Store.get('session').currentUser;
        },

        emailIdnFilter() {
            return HeyFrame.Filter.getByName('decode-idn-email');
        },
    },

    watch: {
        'customer.accountType'(value) {
            if (value === CUSTOMER.ACCOUNT_TYPE_BUSINESS || !this.customerCompanyError) {
                return;
            }

            HeyFrame.Store.get('error').removeApiError(`customer.${this.customer.id}.company`);
        },
    },

    methods: {
        getMailTo(mail) {
            return `mailto:${mail}`;
        },

        async onImitateCustomer() {
            if (this.hasSingleBoundChannelUrl) {
                this.contextStoreService
                    .generateImitateCustomerToken(this.customer.id, this.customer.boundChannel.id)
                    .then((response) => {
                        const handledResponse = ApiService.handleResponse(response);

                        this.contextStoreService.redirectToChannelUrl(
                            this.customer.boundChannel.domains.first().url,
                            handledResponse.token,
                            this.customer.id,
                            this.currentUser?.id,
                        );
                    })
                    .catch(() => {
                        this.createNotificationError({
                            message: this.$tc('sw-customer.detail.notificationImitateCustomerErrorMessage'),
                        });
                    });
                return;
            }

            this.showImitateCustomerModal = true;
        },

        onCloseImitateCustomerModal() {
            this.showImitateCustomerModal = false;
        },
    },
};
