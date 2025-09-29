import template from './sw-navigation-view.html.twig';
import './sw-navigation-view.scss';
import errorConfig from '../../error-config.json';

const { mapPageErrors } = HeyFrame.Component.getComponentHelper();

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['acl'],

    mixins: [
        'placeholder',
    ],

    props: {
        isLoading: {
            type: Boolean,
            required: true,
            default: false,
        },
        type: {
            type: String,
            required: false,
            default: 'page',
        },
    },

    computed: {
        navigation() {
            return HeyFrame.Store.get('swCategoryDetail').navigation;
        },

        isCategoryColumn() {
            return HeyFrame.Store.get('swCategoryDetail').isCategoryColumn;
        },

        cmsPage() {
            if (this.type === 'folder' || this.type === 'link') {
                return false;
            }

            return HeyFrame.Store.get('cmsPage').currentPage;
        },

        isPage() {
            return this.type !== 'folder' && this.type !== 'link';
        },

        isCustomEntity() {
            return this.type === 'custom_entity';
        },

        ...mapPageErrors(errorConfig),
    },
};
