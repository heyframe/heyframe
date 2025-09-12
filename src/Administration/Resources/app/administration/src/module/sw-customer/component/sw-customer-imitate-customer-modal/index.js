/**
 * @sw-package checkout
 */

import ApiService from 'src/core/service/api.service';
import template from './sw-customer-imitate-customer-modal.html.twig';
import './sw-customer-imitate-customer-modal.scss';

const { Mixin } = HeyFrame;
const { Criteria } = HeyFrame.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'contextStoreService',
    ],

    emits: ['modal-close'],

    mixins: [
        Mixin.getByName('notification'),
    ],

    props: {
        customer: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            channelDomains: [],
        };
    },

    computed: {
        modalTitle() {
            return this.$tc('sw-customer.imitateCustomerModal.modalTitle', {
                firstname: this.customer.firstName,
                lastname: this.customer.lastName,
            });
        },

        modalDescription() {
            return this.$tc('sw-customer.imitateCustomerModal.modalDescription', {
                firstname: this.customer.firstName,
                lastname: this.customer.lastName,
            });
        },

        channelDomainRepository() {
            return this.repositoryFactory.create('channel_domain');
        },

        currentUser() {
            return HeyFrame.Store.get('session').currentUser;
        },

        channelDomainCriteria() {
            const criteria = new Criteria();
            criteria.addAssociation('channel');
            criteria.addFilter(Criteria.equals('channel.typeId', HeyFrame.Defaults.storefrontChannelTypeId));
            criteria.addFilter(Criteria.equals('channel.active', true));
            criteria.addSorting(Criteria.sort('channel.name', 'ASC'));
            criteria.addSorting(Criteria.sort('languageId', 'DESC'));

            if (this.customer.boundChannelId) {
                criteria.addFilter(Criteria.equals('channelId', this.customer.boundChannelId));
            }

            return criteria;
        },

        hasChannelDomains() {
            return this.channelDomains !== null && this.channelDomains.length > 0;
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            this.fetchChannelDomains();
        },

        async onChannelDomainMenuItemClick(channelId, channelDomainUrl) {
            this.contextStoreService
                .generateImitateCustomerToken(this.customer.id, channelId)
                .then((response) => {
                    const handledResponse = ApiService.handleResponse(response);

                    this.contextStoreService.redirectToChannelUrl(
                        channelDomainUrl,
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
        },

        onCancel() {
            this.$emit('modal-close');
        },

        fetchChannelDomains() {
            this.channelDomainRepository
                .search(this.channelDomainCriteria, HeyFrame.Context.api)
                .then((loadedDomains) => {
                    this.channelDomains = loadedDomains;
                });
        },
    },
};
