/**
 * @sw-package discovery
 */

import template from './sw-channel-list.html.twig';
import './sw-channel-list.scss';

const { Mixin, Defaults } = HeyFrame;
const { Criteria } = HeyFrame.Data;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
        'domainLinkService',
    ],

    mixins: [
        Mixin.getByName('listing'),
    ],

    data() {
        return {
            channels: null,
            productsForChannel: {},
            isLoading: true,
            sortBy: 'name',
            searchConfigEntity: 'channel',
            lastSortedColumn: null,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    computed: {
        channelColumns() {
            const columns = [
                {
                    property: 'name',
                    dataIndex: 'name',
                    allowResize: true,
                    routerLink: 'sw.channel.detail',
                    label: 'sw-channel.list.columnName',
                    primary: true,
                },
                {
                    property: 'status',
                    dataIndex: 'status',
                    allowResize: true,
                    sortable: false,
                    label: 'sw-channel.list.columnStatus',
                },
                {
                    property: 'id',
                    dataIndex: 'id',
                    allowResize: true,
                    sortable: false,
                    label: 'sw-channel.list.columnFavourite',
                    align: 'center',
                },
                {
                    property: 'createdAt',
                    dataIndex: 'createdAt',
                    allowResize: true,
                    label: 'sw-channel.list.columnCreatedAt',
                },
            ];

            columns.splice(1, 0, {
                property: 'type.name',
                dataIndex: 'type.name',
                allowResize: true,
                label: 'sw-channel.list.columnType',
            });

            return columns;
        },

        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        channelCriteria() {
            const channelCriteria = new Criteria(this.page, this.limit);

            channelCriteria.setTerm(this.term);
            channelCriteria.addSorting(Criteria.sort(this.sortBy, this.sortDirection, this.naturalSorting));
            channelCriteria.addAssociation('type');
            channelCriteria.addAssociation('domains');

            return channelCriteria;
        },

        channelFavoritesService() {
            return HeyFrame.Service('channelFavorites');
        },

        /**
         * @deprecated tag:v6.8.0 - Will be removed, because the filter is unused
         */
        dateFilter() {
            return HeyFrame.Filter.getByName('date');
        },
    },

    methods: {
        onAddChannel() {
            HeyFrame.Utils.EventBus.emit('sw-channel-list-add-new-channel');
        },

        async getList() {
            this.isLoading = true;

            const criteria = await this.addQueryScores(this.term, this.channelCriteria);
            if (!this.entitySearchable) {
                this.isLoading = false;
                this.total = 0;

                return false;
            }

            if (this.freshSearchTerm) {
                criteria.resetSorting();
            }

            return this.channelRepository.search(criteria).then((searchResult) => {
                this.channels = searchResult;
                this.total = searchResult.total;
                this.isLoading = false;
            });
        },

        checkForDomainLink(channel) {
            const domainLink = this.domainLinkService.getDomainLink(channel);

            if (domainLink === null) {
                return false;
            }

            channel.domainLink = domainLink;

            return true;
        },

        openStorefrontLink(storeFrontLink) {
            window.open(storeFrontLink, '_blank');
        },

        isFavorite(channelId) {
            return this.channelFavoritesService.isFavorite(channelId);
        },

        isStorefrontChannel(channel) {
            return channel.type.id === Defaults.storefrontChannelTypeId;
        },
    },
};
