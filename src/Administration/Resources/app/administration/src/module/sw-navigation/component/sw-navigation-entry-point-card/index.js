import template from './sw-navigation-entry-point-card.html.twig';
import './sw-navigation-entry-point-card.scss';

const { Context } = HeyFrame;
const { Criteria, EntityCollection } = HeyFrame.Data;

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
    ],

    props: {
        navigation: {
            type: Object,
            required: true,
        },

        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },
    },

    data() {
        return {
            selectedEntryPoint: this.getInitialEntryPointFromCategory(),
            initialNavigationChannels: this.navigation.navigationChannels,
            addedNavigationChannels: new EntityCollection('/channel', 'channel', Context.api),
            configureHomeModalVisible: false,
        };
    },

    computed: {
        entryPoints() {
            return [
                {
                    value: 'navigationChannels',
                    label: this.$tc('sw-navigation.base.entry-point-card.types.labelMainNavigation'),
                },
                {
                    value: 'footerChannels',
                    label: this.$tc('sw-navigation.base.entry-point-card.types.labelFooterNavigation'),
                },
                {
                    value: 'serviceChannels',
                    label: this.$tc('sw-navigation.base.entry-point-card.types.labelServiceNavigation'),
                },
            ];
        },

        associatedCollection() {
            if (this.hasExistingNavigation) {
                return this.addedNavigationChannels;
            }

            return this.navigation[this.selectedEntryPoint];
        },

        helpText() {
            switch (this.selectedEntryPoint) {
                case 'navigationChannels':
                    return this.$tc('sw-navigation.base.entry-point-card.types.helpTextMainNavigation');
                case 'footerChannels':
                    return this.$tc('sw-navigation.base.entry-point-card.types.helpTextFooterNavigation');
                case 'serviceChannels':
                    return this.$tc('sw-navigation.base.entry-point-card.types.helpTextServiceNavigation');
                default:
                    return '';
            }
        },

        hasExistingNavigation() {
            return this.initialNavigationChannels.length > 0;
        },

        ChannelSelectionLabel() {
            if (this.hasExistingNavigation) {
                return this.$tc('sw-navigation.base.entry-point-card.labelChannelsAdd');
            }

            return this.$tc('global.entities.channel', 2);
        },

        ChannelCriteria() {
            const criteria = new Criteria(1, 25);

            if (this.hasExistingNavigation) {
                criteria.addFilter(
                    Criteria.not('or', [
                        Criteria.equalsAny('id', this.initialNavigationChannels.getIds()),
                    ]),
                );
            }

            return criteria;
        },
    },

    watch: {
        navigation(newCategory) {
            this.initialNavigationChannels = newCategory.navigationChannels;
            this.addedNavigationChannels = new EntityCollection('/channel', 'channel', Context.api);
            this.selectedEntryPoint = this.getInitialEntryPointFromCategory();
        },
    },

    methods: {
        getInitialEntryPointFromCategory() {
            if (this.navigation.navigationChannels && this.navigation.navigationChannels.length > 0) {
                return 'navigationChannels';
            }

            if (this.navigation.footerChannels && this.navigation.footerChannels.length > 0) {
                return 'footerChannels';
            }

            if (this.navigation.serviceChannels && this.navigation.serviceChannels.length > 0) {
                return 'serviceChannels';
            }

            return '';
        },

        onEntryPointChange() {
            this.resetChannelCollections();
        },

        onChannelChange(changedEntityCollection) {
            const entryPoint = this.selectedEntryPoint;

            if (this.hasExistingNavigation) {
                const joinedNavigationCollection = EntityCollection.fromCollection(this.initialNavigationChannels);
                changedEntityCollection.forEach((item) => {
                    joinedNavigationCollection.add(item);
                });
                this.addedNavigationChannels = changedEntityCollection;
                changedEntityCollection = joinedNavigationCollection;
            }

            changedEntityCollection.source = this.navigation[entryPoint].source;
            this.resetChannelCollections();

            this.navigation[entryPoint] = changedEntityCollection;
        },

        resetChannelCollections() {
            const entryPoint = this.selectedEntryPoint;

            const ChannelsCollectionToReset = this.entryPoints.reduce((accumulator, { value }) => {
                if (value === entryPoint) {
                    return accumulator;
                }

                accumulator.push(this.navigation[value]);
                return accumulator;
            }, []);

            ChannelsCollectionToReset.forEach((collection) => {
                const ids = collection.getIds();

                ids.forEach((id) => {
                    collection.remove(id);
                });
            });
        },

        openConfigureHomeModal() {
            this.configureHomeModalVisible = true;
        },

        closeConfigureHomeModal() {
            this.configureHomeModalVisible = false;
        },
    },
};
