import template from './sw-order-list.html.twig';
import './sw-order-list.scss';

/**
 * @sw-package checkout
 */

const { Mixin } = HeyFrame;
const { Criteria } = HeyFrame.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'stateStyleDataProviderService',
        'acl',
        'filterFactory',
        'feature',
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            orders: [],
            sortBy: 'orderDateTime',
            sortDirection: 'DESC',
            isLoading: false,
            /**
             * @deprecated tag:v6.8.0 - will be removed without replacement
             */
            filterLoading: false,
            showDeleteModal: false,
            filterCriteria: [],
            defaultFilters: [
                'order-number-filter',
                'customer-number-filter',
                'affiliate-code-filter',
                'campaign-code-filter',
                'promotion-code-filter',
                'document-filter',
                'order-date-filter',
                'order-value-filter',
                'status-filter',
                'payment-status-filter',
                'delivery-status-filter',
                'payment-method-filter',
                'shipping-method-filter',
                'channel-filter',
                'billing-country-filter',
                'customer-group-filter',
                'shipping-country-filter',
                'customer-group-filter',
                'tag-filter',
                'line-item-filter',
            ],
            storeKey: 'grid.filter.order',
            activeFilterNumber: 0,
            showBulkEditModal: false,
            searchConfigEntity: 'order',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        orderRepository() {
            return this.repositoryFactory.create('order');
        },

        orderColumns() {
            return this.getOrderColumns();
        },

        orderCriteria() {
            const criteria = new Criteria(this.page, this.limit);

            criteria.setTerm(this.term);

            this.sortBy.split(',').forEach((sortBy) => {
                criteria.addSorting(Criteria.sort(sortBy, this.sortDirection));
            });

            this.filterCriteria.forEach((filter) => {
                criteria.addFilter(filter);
            });

            criteria.addAssociation('channel');
            criteria.addAssociation('orderCustomer');
            criteria.addAssociation('currency');
            criteria.addAssociation('stateMachineState');
            criteria.addAssociation('primaryOrderTransaction.stateMachineState');

            return criteria;
        },

        listFilterOptions() {
            return {
                'order-number-filter': {
                    property: 'orderNumber',
                    type: 'string-filter',
                    label: this.$tc('sw-order.filters.orderNumberFilter.label'),
                    placeholder: this.$tc('sw-order.filters.orderNumberFilter.placeholder'),
                    valueProperty: 'key',
                    labelProperty: 'key',
                    criteriaFilterType: 'equalsAny',
                },
                'channel-filter': {
                    property: 'channel',
                    label: this.$tc('sw-order.filters.channelFilter.label'),
                    placeholder: this.$tc('sw-order.filters.channelFilter.placeholder'),
                },
                'order-value-filter': {
                    property: 'amountTotal',
                    type: 'number-filter',
                    label: this.$tc('sw-order.filters.orderValueFilter.label'),
                    fromFieldLabel: null,
                    toFieldLabel: null,
                    fromPlaceholder: this.$tc('global.default.from'),
                    toPlaceholder: this.$tc('global.default.to'),
                },
                'payment-status-filter': {
                    property: 'primaryOrderTransaction.stateMachineState',
                    criteria: this.getStatusCriteria('order_transaction.state'),
                    label: this.$tc('sw-order.filters.paymentStatusFilter.label'),
                    placeholder: this.$tc('sw-order.filters.paymentStatusFilter.placeholder'),
                },
                'status-filter': {
                    property: 'stateMachineState',
                    criteria: this.getStatusCriteria('order.state'),
                    label: this.$tc('sw-order.filters.statusFilter.label'),
                    placeholder: this.$tc('sw-order.filters.statusFilter.placeholder'),
                },
                'order-date-filter': {
                    property: 'orderDateTime',
                    label: this.$tc('sw-order.filters.orderDateFilter.label'),
                    dateType: 'date',
                    fromFieldLabel: null,
                    toFieldLabel: null,
                    showTimeframe: true,
                },
                'customer-number-filter': {
                    property: 'orderCustomer.customer.customerNumber',
                    type: 'string-filter',
                    label: this.$tc('sw-order.filters.customerNumberFilter.label'),
                    placeholder: this.$tc('sw-order.filters.customerNumberFilter.placeholder'),
                    valueProperty: 'key',
                    labelProperty: 'key',
                    criteriaFilterType: 'equals',
                },
                'tag-filter': {
                    property: 'tags',
                    label: this.$tc('sw-order.filters.tagFilter.label'),
                    placeholder: this.$tc('sw-order.filters.tagFilter.placeholder'),
                },
                'promotion-code-filter': {
                    property: 'lineItems.payload.code',
                    type: 'string-filter',
                    label: this.$tc('sw-order.filters.promotionCodeFilter.label'),
                    placeholder: this.$tc('sw-order.filters.promotionCodeFilter.placeholder'),
                    valueProperty: 'key',
                    labelProperty: 'key',
                },
                'payment-method-filter': {
                    property: 'primaryOrderTransaction.paymentMethod',
                    label: this.$tc('sw-order.filters.paymentMethodFilter.label'),
                    placeholder: this.$tc('sw-order.filters.paymentMethodFilter.placeholder'),
                },
                'customer-group-filter': {
                    property: 'orderCustomer.customer.group',
                    label: this.$tc('sw-order.filters.customerGroupFilter.label'),
                    placeholder: this.$tc('sw-order.filters.customerGroupFilter.placeholder'),
                },
                'line-item-filter': {
                    property: 'lineItems.product',
                    label: this.$tc('sw-order.filters.productFilter.label'),
                    placeholder: this.$tc('sw-order.filters.productFilter.placeholder'),
                    criteria: this.productCriteria,
                    displayVariants: true,
                },
            };
        },

        listFilters() {
            return this.filterFactory.create('order', this.listFilterOptions);
        },

        productCriteria() {
            const productCriteria = new Criteria(1, 25);
            productCriteria.addAssociation('options.group');

            return productCriteria;
        },

        currencyFilter() {
            return HeyFrame.Filter.getByName('currency');
        },

        /**
         * @deprecated tag:v6.8.0 - Will be removed, because the filter is unused
         */
        dateFilter() {
            return HeyFrame.Filter.getByName('date');
        },

        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },
    },

    watch: {
        orderCriteria: {
            handler() {
                this.getList();
            },
            deep: true,
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {},

        /**
         * @deprecated tag:v6.8.0 - will be removed, use order.primaryOrderDelivery instead
         */
        deliveryTooltip(deliveries) {
            return deliveries
                .map((delivery) => {
                    return `${delivery.shippingOrderAddress.street},
                        ${delivery.shippingOrderAddress.zipcode}
                        ${delivery.shippingOrderAddress.city}`;
                })
                .join('<hr style="margin: 8px 0">');
        },

        onEdit(order) {
            if (order?.id) {
                this.$router.push({
                    name: 'sw.order.detail',
                    params: {
                        id: order.id,
                    },
                });
            }
        },

        onInlineEditSave(order) {
            order.save();
        },

        onChangeLanguage() {
            this.getList();
        },

        async getList() {
            this.isLoading = true;

            let criteria = await HeyFrame.Service('filterService').mergeWithStoredFilters(this.storeKey, this.orderCriteria);

            criteria = await this.addQueryScores(this.term, criteria);

            this.activeFilterNumber = criteria.filters.length;

            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;

                return;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            try {
                const response = await this.orderRepository.search(criteria);

                this.total = response.total;
                this.orders = response;
                this.isLoading = false;
            } catch {
                this.isLoading = false;
            }
        },

        /**
         * @deprecated tag:v6.8.0 - will be removed, use order.billingAddress instead
         */
        getBillingAddress(order) {
            return order.addresses.find((address) => {
                return address.id === order.billingAddressId;
            });
        },

        disableDeletion(order) {
            if (!this.acl.can('order.deleter')) {
                return true;
            }

            return order.documents.length > 0;
        },

        getOrderColumns() {
            return [
                {
                    property: 'orderNumber',
                    label: 'sw-order.list.columnOrderNumber',
                    routerLink: 'sw.order.detail',
                    allowResize: true,
                    primary: true,
                },
                {
                    property: 'channel.name',
                    label: 'sw-order.list.columnChannel',
                    allowResize: true,
                    visible: false,
                },
                {
                    property: 'orderCustomer.nickname',
                    dataIndex: 'orderCustomer.lastName,orderCustomer.nickname',
                    label: 'sw-order.list.columnCustomerName',
                    allowResize: true,
                },
                {
                    property: 'orderCustomer.company',
                    label: 'sw-order.list.columnCustomerCompany',
                    allowResize: true,
                    visible: false,
                },
                {
                    property: 'amountTotal',
                    label: 'sw-order.list.columnAmount',
                    align: 'right',
                    allowResize: true,
                },
                {
                    property: 'stateMachineState.name',
                    label: 'sw-order.list.columnState',
                    allowResize: true,
                },
                {
                    property: 'primaryOrderTransaction.stateMachineState.name',
                    label: 'sw-order.list.columnTransactionState',
                    allowResize: true,
                },
                {
                    property: 'orderDateTime',
                    label: 'sw-order.list.orderDate',
                    allowResize: true,
                },
            ];
        },

        getVariantFromOrderState(order) {
            const style = this.stateStyleDataProviderService.getStyle('order.state', order.stateMachineState.technicalName);

            return style.colorCode;
        },

        getVariantFromPaymentState(order) {
            let technicalName = order.primaryOrderTransaction?.stateMachineState.technicalName;

            return this.stateStyleDataProviderService.getStyle('order_transaction.state', technicalName).colorCode;
        },

        getVariantFromDeliveryState(order) {
            let technicalName = order.primaryOrderDelivery?.stateMachineState.technicalName;

            if (!HeyFrame.Feature.isActive('v6.8.0.0')) {
                technicalName = this.getDelivery(order).stateMachineState.technicalName;
            }

            return this.stateStyleDataProviderService.getStyle('order_delivery.state', technicalName).colorCode;
        },

        onDelete(id) {
            this.showDeleteModal = id;
        },

        onCloseDeleteModal() {
            this.showDeleteModal = false;
        },

        onConfirmDelete(id) {
            this.showDeleteModal = false;

            return this.orderRepository.delete(id).then(() => {
                this.$refs.orderGrid.resetSelection();
                this.getList();
            });
        },

        updateCriteria(criteria) {
            this.page = 1;

            this.filterCriteria = criteria;
        },

        getStatusCriteria(value) {
            const criteria = new Criteria(1, 25);

            criteria.addFilter(Criteria.equals('stateMachine.technicalName', value));

            return criteria;
        },

        async onBulkEditItems() {
            await this.$nextTick();

            const ordersExcludeDelivery = Object.values(this.$refs.orderGrid.selection).filter((order) => {
                if (!HeyFrame.Feature.isActive('v6.8.0.0')) {
                    return !this.getDelivery(order);
                }
                return !order.primaryOrderDelivery;
            });
            const excludeDelivery = ordersExcludeDelivery.length > 0 ? '1' : '0';

            this.$router.push({
                name: 'sw.bulk.edit.order',
                params: {
                    excludeDelivery,
                },
            });
        },

        /**
         * @deprecated tag:v6.8.0 - will be removed, use order.primaryOrderTransaction instead
         */
        transaction(order) {
            if (HeyFrame.Feature.isActive('v6.8.0.0')) {
                return order.primaryOrderTransaction;
            }

            for (let i = 0; i < order.transactions.length; i += 1) {
                if (
                    ![
                        'cancelled',
                        'failed',
                    ].includes(order.transactions[i].stateMachineState.technicalName)
                ) {
                    return order.transactions[i];
                }
            }

            return order.transactions.last();
        },

        /**
         * @deprecated tag:v6.8.0 - will be removed, use order.primaryOrderDelivery instead
         */
        getDelivery(order) {
            if (!HeyFrame.Feature.isActive('v6.8.0.0')) {
                return order.deliveries[0];
            }

            return order.primaryOrderDelivery;
        },
    },
};
