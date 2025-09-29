import template from './sw-navigation-entry-point-modal.html.twig';
import './sw-navigation-entry-point-modal.scss';

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
        'cmsPageTypeService',
    ],

    emits: ['modal-close'],

    props: {
        ChannelCollection: {
            type: Array,
            required: true,
        },
    },

    data() {
        return {
            temporaryCollection: [],
            ChannelOptions: [],
            selectedChannelId: '',
            showLayoutSelectionModal: false,
            pageTypes: [
                'page',
                'landingpage',
                'product_list',
            ],
            nextRoute: null,
            isDisplayingLeavePageWarning: false,
        };
    },

    computed: {
        selectedChannel() {
            return this.temporaryCollection.find((channel) => channel.id === this.selectedChannelId);
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.ChannelCollection.forEach((Channel) => {
                this.temporaryCollection.push({
                    id: Channel.id,
                    name: Channel.name,
                    homeEnabled: Channel.homeEnabled,
                    homeName: Channel.homeName,
                    homeMetaTitle: Channel.homeMetaTitle,
                    homeMetaDescription: Channel.homeMetaDescription,
                    homeKeywords: Channel.homeKeywords,
                    homeCmsPageId: Channel.homeCmsPageId,
                    homeCmsPage: Channel.homeCmsPage ? { ...Channel.homeCmsPage } : null,
                    translated: Channel.translated ? { ...Channel.translated } : null,
                });

                this.ChannelOptions.push({
                    value: Channel.id,
                    label: Channel.translated ? Channel.translated.name : Channel.name,
                });
            });

            if (this.ChannelCollection.length > 0) {
                this.selectedChannelId = this.ChannelOptions[0].value;
            }
        },

        closeModal() {
            this.$emit('modal-close');
        },

        getCmsPageTypeName(name) {
            const fallback = this.$tc('sw-navigation.base.cms.defaultDesc');

            if (!name) {
                return fallback;
            }

            const nameSnippetKey = this.cmsPageTypeService.getType(name)?.title;
            return nameSnippetKey ? this.$tc(nameSnippetKey) : fallback;
        },

        onLayoutSelect(layoutId, layout) {
            this.selectedChannel.homeCmsPage = layout;
            this.selectedChannel.homeCmsPageId = layoutId;
        },

        onLayoutReset() {
            this.onLayoutSelect(null, null);
        },

        openInPagebuilder() {
            let to = { name: 'sw.cms.create' };
            if (this.selectedChannel.homeCmsPage) {
                to = {
                    name: 'sw.cms.detail',
                    params: { id: this.selectedChannel.homeCmsPageId },
                };
            }

            if (this.hasNotAppliedChanges()) {
                this.isDisplayingLeavePageWarning = true;
                this.nextRoute = to;
                return;
            }

            this.closeModal();

            this.$nextTick(() => {
                this.$router.push(to);
            });
        },

        openLayoutModal() {
            if (!this.acl.can('navigation.editor')) {
                return;
            }

            this.showLayoutSelectionModal = true;
        },

        closeLayoutModal() {
            this.showLayoutSelectionModal = false;
        },

        applyChanges() {
            for (let i = 0; i < this.temporaryCollection.length; i += 1) {
                const tempChannel = this.temporaryCollection[i];
                const realChannel = this.ChannelCollection[i];

                realChannel.name = tempChannel.name;
                realChannel.homeEnabled = tempChannel.homeEnabled;
                realChannel.homeName = tempChannel.homeName;
                realChannel.homeMetaTitle = tempChannel.homeMetaTitle;
                realChannel.homeMetaDescription = tempChannel.homeMetaDescription;
                realChannel.homeKeywords = tempChannel.homeKeywords;
                realChannel.homeCmsPageId = tempChannel.homeCmsPageId;
                realChannel.homeCmsPage = tempChannel.homeCmsPage;
            }

            this.closeModal();
        },

        hasNotAppliedChanges() {
            for (let i = 0; i < this.temporaryCollection.length; i += 1) {
                const original = this.ChannelCollection[i];
                const copy = this.temporaryCollection[i];

                if (
                    this.isAttributeEqual(copy.name, original.name) ||
                    this.isAttributeEqual(copy.homeEnabled, original.homeEnabled) ||
                    this.isAttributeEqual(copy.homeName, original.homeName) ||
                    this.isAttributeEqual(copy.homeMetaTitle, original.homeMetaTitle) ||
                    this.isAttributeEqual(copy.homeMetaDescription, original.homeMetaDescription) ||
                    this.isAttributeEqual(copy.homeKeywords, original.homeKeywords) ||
                    this.isAttributeEqual(copy.homeCmsPageId, original.homeCmsPageId)
                ) {
                    return true;
                }
            }

            return false;
        },

        isAttributeEqual(copy, original) {
            if (copy === null) {
                copy = '';
            }

            if (original === null) {
                original = '';
            }

            return copy !== original;
        },

        onLeaveModalClose() {
            this.nextRoute = null;
            this.isDisplayingLeavePageWarning = false;
        },

        onLeaveModalConfirm(destination) {
            this.isDisplayingLeavePageWarning = false;
            this.$nextTick(() => {
                this.closeModal();
                this.$nextTick(() => {
                    this.$router.push({
                        name: destination.name,
                        params: destination.params,
                    });
                });
            });
        },
    },
};
