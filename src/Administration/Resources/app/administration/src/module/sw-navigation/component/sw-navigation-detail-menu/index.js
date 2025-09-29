import template from './sw-navigation-detail-menu.html.twig';

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'acl',
        'repositoryFactory',
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
            showMediaModal: false,
        };
    },

    computed: {
        reversedVisibility: {
            get() {
                return !this.navigation.visible;
            },
            set(visibility) {
                this.navigation.visible = !visibility;
            },
        },

        mediaItem() {
            return this.navigation !== null ? this.navigation.media : null;
        },

        mediaRepository() {
            return this.repositoryFactory.create('media');
        },
    },

    methods: {
        onMediaSelectionChange(mediaItems) {
            const media = mediaItems[0];
            if (!media) {
                return;
            }

            this.mediaRepository.get(media.id).then((updatedMedia) => {
                this.navigation.mediaId = updatedMedia.id;
                this.navigation.media = updatedMedia;
            });
        },

        onSetMediaItem({ targetId }) {
            this.mediaRepository.get(targetId).then((updatedMedia) => {
                this.navigation.mediaId = targetId;
                this.navigation.media = updatedMedia;
            });
        },

        onRemoveMediaItem() {
            this.navigation.mediaId = null;
            this.navigation.media = null;
        },

        onMediaDropped(dropItem) {
            // to be consistent refetch entity with repository
            this.onSetMediaItem({ targetId: dropItem.id });
        },
    },
};
