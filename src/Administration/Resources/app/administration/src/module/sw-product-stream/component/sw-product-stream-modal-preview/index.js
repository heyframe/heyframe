/*
 * @sw-package inventory
 */

import shuffle from 'lodash/shuffle';
import template from './sw-product-stream-modal-preview.html.twig';
import './sw-product-stream-modal-preview.scss';

const { Context } = HeyFrame;
const { Criteria } = HeyFrame.Data;
const PRODUCT_COMPARISON_SALES_CHANNEL_TYPE_ID = 'ed535e5722134ac1aa6524f73e26881b';

/**
 * @private
 */
export default {
    template,

    inject: [
        'repositoryFactory',
        'productStreamPreviewService',
    ],

    emits: ['modal-close'],

    props: {
        filters: {
            type: Array,
            required: true,
        },
        defaultLimit: {
            type: Number,
            default: 25,
        },
        defaultSorting: {
            type: String,
            default: null,
            validator(value) {
                return value === null || value.split(':').length === 2;
            },
        },
    },
    data() {
        return {
            products: [],
            selectedChannel: null,
            searchTerm: '',
            page: 1,
            total: false,
            limit: this.defaultLimit,
            sorting: this.defaultSorting,
            isLoading: false,
            selectedCurrencyIsoCode: 'EUR',
            selectedCurrencyId: Context.app.systemCurrencyId,
        };
    },

    computed: {
        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        channelCriteria() {
            return new Criteria(1, 1)
                .addFilter(
                    Criteria.not('OR', [
                        Criteria.equals('typeId', PRODUCT_COMPARISON_SALES_CHANNEL_TYPE_ID),
                    ]),
                )
                .addSorting(Criteria.sort('type.iconName', 'ASC'));
        },

        previewCriteria() {
            const criteria = new Criteria(this.page, this.limit).setTerm(this.searchTerm);

            if (this.sorting) {
                if (this.sorting === 'random') {
                    this.addRandomSort(criteria);
                } else {
                    const [
                        field,
                        direction,
                    ] = this.sorting.split(':');
                    criteria.addSorting(Criteria.sort(field, direction));
                }
            }

            return criteria;
        },

        previewSelectionCriteria() {
            return new Criteria()
                .addFilter(
                    Criteria.not('OR', [
                        Criteria.equals('typeId', PRODUCT_COMPARISON_SALES_CHANNEL_TYPE_ID),
                    ]),
                )
                .addSorting(Criteria.sort('name', 'ASC'));
        },

        productColumns() {
            return [
                {
                    property: 'name',
                    label: this.$tc('sw-product-stream.filter.values.product'),
                    type: 'text',
                    routerLink: 'sw.product.detail',
                },
                {
                    property: 'manufacturer.name',
                    label: this.$tc('sw-product-stream.filter.values.manufacturerId'),
                },
                {
                    property: 'active',
                    label: this.$tc('sw-product-stream.filter.values.active'),
                    align: 'center',
                    type: 'bool',
                },
                {
                    property: 'price',
                    label: this.$tc('sw-product-stream.filter.values.price'),
                },
                {
                    property: 'stock',
                    label: this.$tc('sw-product-stream.filter.values.stock'),
                    align: 'right',
                },
            ];
        },

        currencyFilter() {
            return HeyFrame.Filter.getByName('currency');
        },

        stockColorVariantFilter() {
            return HeyFrame.Filter.getByName('stockColorVariant');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.isLoading = true;

            return this.loadChannels()
                .then(() => {
                    return this.loadEntityData();
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        onSearchTermChange(searchTerm) {
            this.searchTerm = searchTerm;
            this.page = 1;
            this.isLoading = true;
            this.loadEntityData().finally(() => {
                this.isLoading = false;
            });
        },

        onChannelChange() {
            this.page = 1;
            this.isLoading = true;
            this.loadChannelById()
                .then(() => {
                    return this.loadEntityData();
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        loadEntityData() {
            if (!this.selectedChannel) {
                return false;
            }

            return this.productStreamPreviewService
                .preview(this.selectedChannel, this.previewCriteria, this.mapFiltersForSearch(this.filters), {
                    'sw-currency-id': this.selectedCurrencyId,
                    'sw-inheritance': true,
                })
                .then((result) => {
                    this.products = Object.values(result.elements);
                    this.total = result.total;
                });
        },

        loadChannels() {
            return this.channelRepository.searchIds(this.channelCriteria).then(({ data }) => {
                this.selectedChannel = data.at(0);
            });
        },

        mapFiltersForSearch(filters = [], parentType = null) {
            return filters.map((condition) => {
                const { field, type, operator, value, parameters, queries } = condition;
                const mappedQueries = this.mapFiltersForSearch(queries, type);
                const mapped = {
                    field,
                    type,
                    operator,
                    value,
                    parameters,
                    queries: mappedQueries,
                };

                if (field === 'id' || field === 'product.id') {
                    const newOperator = this.isNotEqualToAnyType(type, parentType) ? 'AND' : 'OR';

                    return {
                        type: 'multi',
                        field: null,
                        operator: newOperator,
                        value: null,
                        parameters: null,
                        queries: [
                            mapped,
                            { ...mapped, ...{ field: 'parentId' } },
                        ],
                    };
                }

                return mapped;
            });
        },

        closeModal() {
            this.$emit('modal-close');
        },

        getPriceForDefaultCurrency(product) {
            const cheapest = product.calculatedCheapestPrice;
            let real = product.calculatedPrice;

            if (product.calculatedPrices.length > 0) {
                real = product.calculatedPrices[product.calculatedPrices.length - 1];
            }

            if (cheapest.unitPrice !== real.unitPrice) {
                return real;
            }

            return cheapest;
        },

        onPageChange({ page = 1, limit = 25 }) {
            this.isLoading = true;

            this.page = page;
            this.limit = limit;

            this.loadEntityData().finally(() => {
                this.isLoading = false;
            });
        },

        loadChannelById() {
            if (this.selectedChannel === null) {
                return Promise.resolve();
            }

            const criteria = this.channelCriteria;

            criteria.addAssociation('currency');

            return this.channelRepository
                .get(this.selectedChannel, HeyFrame.Context.api, this.channelCriteria)
                .then((channel) => {
                    this.selectedCurrencyIsoCode = channel.currency.isoCode;
                    this.selectedCurrencyId = channel.currencyId;
                });
        },

        isNotEqualToAnyType(type, parentType) {
            return type === 'equalsAny' && parentType === 'not';
        },

        addRandomSort(criteria) {
            let fields = [
                'name',
                'createdAt',
                'cheapestPrice',
                'releaseDate',
            ];

            fields = shuffle(fields);
            const selectedFields = fields.slice(0, 2);
            const directions = [
                'ASC',
                'DESC',
            ];
            const randomDirection = directions[Math.floor(Math.random() * directions.length)];

            selectedFields.forEach((field) => {
                criteria.addSorting(Criteria.sort(field, randomDirection));
            });
        },
    },
};
