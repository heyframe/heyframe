import template from './sw-navigation-detail-cms.html.twig';
import './sw-navigation-detail-cms.scss';

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

        cmsPage() {
            return HeyFrame.Store.get('cmsPage').currentPage;
        },
    },
};
