import template from './sw-navigation-detail-seo.html.twig';

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['acl'],

    props: {
        isLoading: {
            type: Boolean,
            required: true,
        },
    },

    computed: {
        navigation() {
            return HeyFrame.Store.get('swCategoryDetail').navigation;
        },
    },
};
