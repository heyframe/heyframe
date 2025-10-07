/**
 * @sw-package discovery
 */

import template from './sw-channel-menu.html.twig';
import './sw-channel-menu.scss';

const { Criteria } = HeyFrame.Data;
const FlatTree = HeyFrame.Helper.FlatTreeHelper;

/**
 * @private
 */
export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
        'domainLinkService',
    ],

    data() {
        return {
            channels: [],
            showModal: false,
            isLoading: true,
        };
    },

    computed: {
        channelRepository() {
            return this.repositoryFactory.create('channel');
        },

        canCreateChannels() {
            return this.acl.can('channel.creator');
        },

        channelCriteria() {
            const criteria = new Criteria(1, 7);

            criteria.addIncludes({
                channel: [
                    'name',
                    'type',
                    'active',
                    'translated',
                    'domains',
                ],
                channel_type: ['iconName'],
                channel_domain: [
                    'url',
                    'languageId',
                ],
            });

            criteria.addSorting(Criteria.sort('channel.name', 'ASC'));
            criteria.addAssociation('type');
            criteria.addAssociation('domains');

            if (this.channelFavorites.length) {
                criteria.setLimit(50);
                criteria.addFilter(Criteria.equalsAny('id', this.channelFavorites));
            }

            return criteria;
        },

        moreChannelAvailable() {
            return this.channels?.total > this.channels?.length;
        },

        buildMenuTree() {
            const flatTree = new FlatTree();

            this.channels.forEach((channel) => {
                flatTree.add({
                    id: channel.id,
                    path: 'sw.channel.detail',
                    params: { id: channel.id },
                    color: '#D8DDE6',
                    label: {
                        label: channel.translated.name,
                        translated: true,
                    },
                    icon: channel.type.iconName,
                    children: [],
                    domainLink: this.getDomainLink(channel),
                    active: channel.active,
                });
            });

            return flatTree.convertToTree();
        },

        moreItemsEntry() {
            return {
                active: true,
                children: [],
                color: '#D8DDE6',
                icon: 'regular-ellipsis-v',
                label: this.$tc('sw-channel.general.titleMenuMoreItems'),
                path: 'sw.channel.list',
                position: -1, // use last position
            };
        },

        channelFavoritesService() {
            return HeyFrame.Service('channelFavorites');
        },

        channelFavorites() {
            if (this.isLoading) {
                return [];
            }

            return this.channelFavoritesService.getFavoriteIds();
        },
    },

    watch: {
        channelFavorites() {
            if (this.isLoading) {
                return;
            }

            this.loadEntityData();
        },
    },

    created() {
        this.createdComponent();
    },

    unmounted() {
        this.destroyedComponent();
    },

    methods: {
        createdComponent() {
            this.registerListener();

            this.channelFavoritesService.initService().finally(() => {
                this.isLoading = false;
            });
        },

        registerListener() {
            HeyFrame.Utils.EventBus.on('sw-channel-detail-channel-change', this.loadEntityData);
            HeyFrame.Utils.EventBus.on('sw-language-switch-change-application-language', this.loadEntityData);
            HeyFrame.Utils.EventBus.on('sw-channel-detail-base-channel-change', this.openChannelModal);
            HeyFrame.Utils.EventBus.on('sw-channel-list-add-new-channel', this.openChannelModal);
        },

        destroyedComponent() {
            HeyFrame.Utils.EventBus.off('sw-channel-detail-channel-change', this.loadEntityData);
            HeyFrame.Utils.EventBus.off('sw-language-switch-change-application-language', this.loadEntityData);
            HeyFrame.Utils.EventBus.off('sw-channel-detail-base-channel-change', this.openChannelModal);
            HeyFrame.Utils.EventBus.off('sw-channel-list-add-new-channel', this.openChannelModal);
        },

        getDomainLink(channel) {
            return this.domainLinkService.getDomainLink(channel);
        },

        loadEntityData() {
            this.channelRepository.search(this.channelCriteria).then((response) => {
                this.channels = response;
            });
        },

        openChannelModal() {
            this.showModal = true;
        },

        openFrontendLink(storeFrontLink) {
            window.open(storeFrontLink, '_blank');
        },
    },
};
