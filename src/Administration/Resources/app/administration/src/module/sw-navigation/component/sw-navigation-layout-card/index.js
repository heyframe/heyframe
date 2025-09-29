import template from './sw-navigation-layout-card.html.twig';
import './sw-navigation-layout-card.scss';

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

    props: {
        navigation: {
            type: Object,
            required: true,
        },

        cmsPage: {
            type: Object,
            required: false,
            default: null,
        },

        isLoading: {
            type: Boolean,
            required: false,
            default: false,
        },

        pageTypes: {
            type: Array,
            required: false,
            default() {
                return [
                    'page',
                    'landingpage',
                    'product_list',
                ];
            },
        },

        headline: {
            type: String,
            required: false,
            default: '',
        },
    },

    data() {
        return {
            showLayoutSelectionModal: false,
        };
    },

    computed: {
        pageTypeTitle() {
            const fallback = this.$tc('sw-navigation.base.cms.defaultDesc');
            if (!this.cmsPage) {
                return fallback;
            }

            const pageType = this.cmsPageTypeService.getType(this.cmsPage.type);
            return pageType ? this.$tc(this.cmsPageTypeService.getType(this.cmsPage.type).title) : fallback;
        },
    },

    methods: {
        onLayoutSelect(selectedLayout) {
            this.navigation.cmsPageId = selectedLayout;
        },

        onLayoutReset() {
            this.onLayoutSelect(null);
        },

        openInPagebuilder() {
            if (!this.cmsPage) {
                this.$router.push({
                    name: 'sw.cms.create',
                    params: { type: 'navigation', id: this.navigation.id },
                });
            } else {
                this.$router.push({
                    name: 'sw.cms.detail',
                    params: { id: this.navigation.cmsPageId },
                });
            }
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
    },
};
