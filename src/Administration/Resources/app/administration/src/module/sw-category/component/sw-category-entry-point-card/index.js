import template from './sw-category-entry-point-card.html.twig';
import './sw-category-entry-point-card.scss';

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
        category: {
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
            initialNavigationChannels: this.category.navigationChannels,
            addedNavigationChannels: new EntityCollection('/channel', 'channel', Context.api),
            configureHomeModalVisible: false,
        };
    },

    computed: {
        entryPoints() {
            return [
                {
                    value: 'navigationChannels',
                    label: this.$tc('sw-category.base.entry-point-card.types.labelMainNavigation'),
                },
                {
                    value: 'footerChannels',
                    label: this.$tc('sw-category.base.entry-point-card.types.labelFooterNavigation'),
                },
                {
                    value: 'serviceChannels',
                    label: this.$tc('sw-category.base.entry-point-card.types.labelServiceNavigation'),
                },
            ];
        },

        associatedCollection() {
            if (this.hasExistingNavigation) {
                return this.addedNavigationChannels;
            }

            return this.category[this.selectedEntryPoint];
        },

        helpText() {
            switch (this.selectedEntryPoint) {
                case 'navigationChannels':
                    return this.$tc('sw-category.base.entry-point-card.types.helpTextMainNavigation');
                case 'footerChannels':
                    return this.$tc('sw-category.base.entry-point-card.types.helpTextFooterNavigation');
                case 'serviceChannels':
                    return this.$tc('sw-category.base.entry-point-card.types.helpTextServiceNavigation');
                default:
                    return '';
            }
        },

        hasExistingNavigation() {
            return this.initialNavigationChannels.length > 0;
        },

        channelSelectionLabel() {
            if (this.hasExistingNavigation) {
                return this.$tc('sw-category.base.entry-point-card.labelChannelsAdd');
            }

            return this.$tc('global.entities.channel', 2);
        },

        channelCriteria() {
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
        category(newCategory) {
            this.initialNavigationChannels = newCategory.navigationChannels;
            this.addedNavigationChannels = new EntityCollection('/channel', 'channel', Context.api);
            this.selectedEntryPoint = this.getInitialEntryPointFromCategory();
        },
    },

    methods: {
        getInitialEntryPointFromCategory() {
            if (this.category.navigationChannels && this.category.navigationChannels.length > 0) {
                return 'navigationChannels';
            }

            if (this.category.footerChannels && this.category.footerChannels.length > 0) {
                return 'footerChannels';
            }

            if (this.category.serviceChannels && this.category.serviceChannels.length > 0) {
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

            changedEntityCollection.source = this.category[entryPoint].source;
            this.resetChannelCollections();

            this.category[entryPoint] = changedEntityCollection;
        },

        resetChannelCollections() {
            const entryPoint = this.selectedEntryPoint;

            const channelsCollectionToReset = this.entryPoints.reduce((accumulator, { value }) => {
                if (value === entryPoint) {
                    return accumulator;
                }

                accumulator.push(this.category[value]);
                return accumulator;
            }, []);

            channelsCollectionToReset.forEach((collection) => {
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
