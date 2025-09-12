import template from './sw-category-detail-base.html.twig';
import './sw-category-detail-base.scss';

const { mapPropertyErrors } = HeyFrame.Component.getComponentHelper();

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
    ],

    mixins: [
        HeyFrame.Mixin.getByName('placeholder'),
    ],

    props: {
        isLoading: {
            type: Boolean,
            required: true,
        },
    },

    computed: {
        customFieldSetsArray() {
            return HeyFrame.Store.get('swCategoryDetail').customFieldSets ?? [];
        },

        ...mapPropertyErrors('category', [
            'name',
            'type',
        ]),

        categoryTypes() {
            return [
                {
                    value: 'page',
                    label: this.$tc('sw-category.base.general.types.page'),
                },
                {
                    value: 'folder',
                    label: this.$tc('sw-category.base.general.types.folder'),
                },
                // eslint-disable-next-line no-warning-comments
                // @todo NEXT-22697 - Re-implement, when re-enabling cms-aware
                // {
                //     value: 'custom_entity',
                //     label: this.$tc('sw-category.base.general.types.customEntity'),
                // },
                {
                    value: 'link',
                    label: this.typeLinkLabel,
                    disabled: this.isChannelEntryPoint,
                },
            ];
        },

        typeLinkLabel() {
            if (this.isChannelEntryPoint) {
                return this.$tc('sw-category.base.general.types.linkUnavailable');
            }

            return this.$tc('sw-category.base.general.types.link');
        },

        categoryTypeHelpText() {
            if (
                [
                    'page',
                    'folder',
                    'link',
                ].includes(this.category.type)
            ) {
                return this.$tc(`sw-category.base.general.types.helpText.${this.category.type}`);
            }

            return null;
        },

        isChannelEntryPoint() {
            return (
                this.category.navigationChannels.length > 0 ||
                this.category.serviceChannels.length > 0 ||
                this.category.footerChannels.length > 0
            );
        },

        category() {
            return HeyFrame.Store.get('swCategoryDetail').category;
        },

        isCategoryColumn() {
            return HeyFrame.Store.get('swCategoryDetail').isCategoryColumn;
        },
    },
};
