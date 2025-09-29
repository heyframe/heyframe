import template from './sw-navigation-detail-base.html.twig';
import './sw-navigation-detail-base.scss';

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

        ...mapPropertyErrors('navigation', [
            'name',
            'type',
        ]),

        navigationTypes() {
            return [
                {
                    value: 'page',
                    label: this.$tc('sw-navigation.base.general.types.page'),
                },
                {
                    value: 'folder',
                    label: this.$tc('sw-navigation.base.general.types.folder'),
                },
                // eslint-disable-next-line no-warning-comments
                // @todo NEXT-22697 - Re-implement, when re-enabling cms-aware
                // {
                //     value: 'custom_entity',
                //     label: this.$tc('sw-navigation.base.general.types.customEntity'),
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
                return this.$tc('sw-navigation.base.general.types.linkUnavailable');
            }

            return this.$tc('sw-navigation.base.general.types.link');
        },

        navigationTypeHelpText() {
            if (
                [
                    'page',
                    'folder',
                    'link',
                ].includes(this.navigation.type)
            ) {
                return this.$tc(`sw-navigation.base.general.types.helpText.${this.navigation.type}`);
            }

            return null;
        },

        isChannelEntryPoint() {
            return (
                this.navigation.navigationChannels.length > 0 ||
                this.navigation.serviceChannels.length > 0 ||
                this.navigation.footerChannels.length > 0
            );
        },

        navigation() {
            return HeyFrame.Store.get('swCategoryDetail').navigation;
        },

        isCategoryColumn() {
            return HeyFrame.Store.get('swCategoryDetail').isCategoryColumn;
        },
    },
};
