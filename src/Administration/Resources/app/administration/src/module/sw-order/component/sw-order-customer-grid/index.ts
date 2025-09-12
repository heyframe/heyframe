import type CriteriaType from 'src/core/data/criteria.data';
import type RepositoryType from '../../../../core/data/repository.data';

import template from './sw-order-customer-grid.html.twig';
import './sw-order-customer-grid.scss';

import type { Cart } from '../../order.types';

/**
 * @sw-package checkout
 */

const { Component, Store, Mixin, Context } = HeyFrame;
const { Criteria } = HeyFrame.Data;

interface GridColumn {
    property: string;
    dataIndex?: string;
    label: string;
    primary?: boolean;
}

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default Component.wrapComponentConfig({
    template,

    inject: [
        'repositoryFactory',
    ],

    mixins: [
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
    ],

    data(): {
        customers: EntityCollection<'customer'> | null;
        isLoading: boolean;
        isSwitchingCustomer: boolean;
        showNewCustomerModal: boolean;
        customer: Entity<'customer'> | null;
        disableRouteParams: boolean;
        showChannelSelectModal: boolean;
        showCustomerChangesModal: boolean;
        channelIds: string[];
        customerDraft: Entity<'customer'> | null;
    } {
        return {
            customers: null,
            isLoading: false,
            isSwitchingCustomer: false,
            showNewCustomerModal: false,
            customer: null,
            customerDraft: null,
            disableRouteParams: true,
            showChannelSelectModal: false,
            showCustomerChangesModal: false,
            channelIds: [],
        };
    },

    computed: {
        customerData(): Entity<'customer'> | null {
            return Store.get('swOrder').customer;
        },

        customerRepository(): RepositoryType<'customer'> {
            return this.repositoryFactory.create('customer');
        },

        customerCriteria(): CriteriaType {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
            const criteria = new Criteria(this.page, this.limit);
            criteria.addAssociation('channel');
            criteria.addAssociation('boundChannel');
            criteria.addSorting(Criteria.sort('createdAt', 'DESC'));

            if (this.term) {
                // eslint-disable-next-line @typescript-eslint/no-unsafe-argument
                criteria.setTerm(this.term);
            }

            return criteria;
        },

        customerCriterion(): CriteriaType {
            const criteria = new Criteria(1, 25);
            criteria
                .addAssociation('addresses')
                .addAssociation('group')
                .addAssociation('salutation')
                .addAssociation('channel.languages')
                .addAssociation('lastPaymentMethod')
                .addAssociation('defaultBillingAddress.country')
                .addAssociation('defaultBillingAddress.countryState')
                .addAssociation('defaultBillingAddress.salutation')
                .addAssociation('defaultShippingAddress.country')
                .addAssociation('defaultShippingAddress.countryState')
                .addAssociation('defaultShippingAddress.salutation')
                .addAssociation('tags')
                .addAssociation('boundChannel');

            return criteria;
        },

        customerColumns(): GridColumn[] {
            return [
                {
                    property: 'select',
                    label: '',
                },
                {
                    property: 'firstName',
                    dataIndex: 'lastName,firstName',
                    label: this.$tc('sw-order.initialModal.customerGrid.columnCustomerName'),
                    primary: true,
                },
                {
                    property: 'customerNumber',
                    label: this.$tc('sw-order.initialModal.customerGrid.columnCustomerNumber'),
                },
                {
                    property: 'channel',
                    label: this.$tc('sw-order.initialModal.customerGrid.columnChannel'),
                },
                {
                    property: 'email',
                    label: this.$tc('sw-order.initialModal.customerGrid.columnEmailAddress'),
                },
            ];
        },

        showEmptyState(): boolean {
            return !this.total && !this.isLoading;
        },

        emptyTitle(): string {
            if (!this.term) {
                return this.$tc('sw-customer.list.messageEmpty');
            }

            // eslint-disable-next-line @typescript-eslint/no-unsafe-assignment
            return this.$t('sw-order.initialModal.customerGrid.textEmptySearch', { name: this.term }, 0);
        },

        cart(): Cart {
            return Store.get('swOrder').cart;
        },

        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },

        channelRepository(): RepositoryType<'channel'> {
            return this.repositoryFactory.create('channel');
        },

        channelCriteria(): CriteriaType {
            const criteria = new Criteria();
            criteria.addFilter(Criteria.equals('active', true));

            if (this.customer?.boundChannelId) {
                criteria.addFilter(Criteria.equals('id', this.customer.boundChannelId));
            }

            return criteria;
        },

        isSelectChannelDisabled(): boolean {
            if (!this.customer?.channelId) {
                return true;
            }

            return !this.channelIds.includes(this.customer.channelId);
        },
    },

    mounted() {
        void this.mountedComponent();
    },

    methods: {
        async mountedComponent(): Promise<void> {
            this.channelIds = await this.loadChannel();

            if (!this.customerData) {
                return;
            }

            // @ts-expect-error
            this.$refs.customerFilter.term = this.customerData?.customerNumber;
            void this.onSearch(this.customerData?.customerNumber);
            void this.onCheckCustomer(this.customerData);
        },

        getList(): Promise<void> {
            this.isLoading = true;
            return this.customerRepository
                .search(this.customerCriteria)
                .then((customers) => {
                    this.customers = customers;
                    // @ts-expect-error
                    this.total = customers.total;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        onShowNewCustomerModal() {
            this.showNewCustomerModal = true;
        },

        isChecked(item: Entity<'customer'>): boolean {
            return item.id === this.customer?.id;
        },

        async onCheckCustomer(item: Entity<'customer'>) {
            // If there's an existing customer, save it as a draft.
            if (this.customer) {
                this.customerDraft = this.customer;
            }

            this.customer = await this.customerRepository.get(item.id, Context.api, this.customerCriterion);

            const isExists = (this.customer?.channel?.languages || []).some(
                (language) => language.id === Context.api.systemLanguageId,
            );

            if (!isExists && this.customer?.channel?.languageId) {
                Store.get('context').api.languageId = this.customer.channel.languageId;
            }

            // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
            if (isExists && !Store.get('context').isSystemDefaultLanguage) {
                Store.get('context').resetLanguageToDefault();
            }

            // If the customer belongs to a sales channel not in the allowed list and has no bound sales channel.
            if (!this.customer?.boundChannelId) {
                this.showChannelSelectModal = true;

                return;
            }

            // If switching to a different customer whose sales channel is different from the current one.
            if (
                this.customerDraft &&
                this.customer?.boundChannelId &&
                this.customerDraft.channelId !== this.customer.boundChannelId
            ) {
                this.showCustomerChangesModal = true;

                return;
            }

            void this.handleSelectCustomer();
        },

        createCart(channelId: string): Promise<void> {
            // eslint-disable-next-line @typescript-eslint/no-unsafe-return
            return Store.get('swOrder').createCart({ channelId });
        },

        setCustomer(customer: Entity<'customer'> | null): void {
            void Store.get('swOrder').selectExistingCustomer({ customer });
        },

        async handleSelectCustomer(): Promise<void> {
            this.isSwitchingCustomer = true;

            try {
                if (!this.cart.token) {
                    // It is compulsory to create cart and get cart token first
                    await this.createCart(this.customer?.channelId ?? '');
                }

                this.setCustomer(this.customer);

                await this.updateCustomerContext();
            } catch {
                // eslint-disable-next-line @typescript-eslint/no-unsafe-call
                this.createNotificationError({
                    message: this.$tc('sw-order.create.messageSwitchCustomerError'),
                });
            } finally {
                this.isSwitchingCustomer = false;
            }
        },

        onAddNewCustomer(customerId: string): void {
            if (!customerId) {
                return;
            }

            // Refresh customer list if new customer is created successfully
            void this.getList();
            this.page = 1;
            this.term = '';
        },

        async updateCustomerContext(): Promise<void> {
            if (!this.customer) return;

            await Store.get('swOrder')
                .updateCustomerContext({
                    customerId: this.customer.id,
                    channelId: this.customer.channelId,
                    contextToken: this.cart.token,
                })
                .then((response) => {
                    // Update cart after customer context is updated
                    // eslint-disable-next-line @typescript-eslint/no-unsafe-member-access
                    if (response.status === 200) {
                        void this.getCart();
                    }
                });
        },

        async getCart(): Promise<void> {
            if (!this.customer) return;

            await Store.get('swOrder').getCart({
                channelId: this.customer.channelId,
                contextToken: this.cart.token,
            });
        },

        async loadChannel(): Promise<string[]> {
            const { data: ids } = await this.channelRepository.searchIds(this.channelCriteria);

            return ids;
        },

        onChannelChange(channelId: string): void {
            if (!this.customer) {
                return;
            }

            this.customer.channelId = channelId;
        },

        onCloseChannelSelectModal() {
            this.customer = this.customerDraft;

            this.showChannelSelectModal = false;
        },

        async onSelectChannel() {
            this.isLoading = true;

            try {
                await this.handleSelectCustomer();
            } finally {
                this.isLoading = false;
                this.showChannelSelectModal = false;
            }
        },

        customerUnavailable(customer: Entity<'customer'>): boolean {
            if (!this.channelIds.length) {
                return true;
            }

            return !!customer?.boundChannelId && !this.channelIds.includes(customer.boundChannelId);
        },

        async onChangeCustomer() {
            this.isLoading = true;
            try {
                await this.handleSelectCustomer();
            } finally {
                this.isLoading = false;
                this.showCustomerChangesModal = false;
            }
        },

        onCloseCustomerChangesModal() {
            this.customer = this.customerDraft;

            this.showCustomerChangesModal = false;
        },
    },
});
