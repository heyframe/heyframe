import template from './sw-heyframe-updates-plugins.html.twig';

/**
 * @sw-package framework
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    template,

    inject: ['feature'],

    props: {
        isLoading: {
            type: Boolean,
        },
        plugins: {
            type: Array,
            default: () => [],
        },
    },
    computed: {
        columns() {
            return [
                {
                    property: 'name',
                    label: this.$tc('sw-settings-heyframe-updates.plugins.columns.name'),
                    rawData: true,
                },
                {
                    property: 'icon',
                    label: this.$tc('sw-settings-heyframe-updates.plugins.columns.available'),
                    rawData: true,
                },
            ];
        },
    },

    methods: {
        openMyExtensions() {
            void this.$router.push({
                name: 'sw.extension.my-extensions.listing.app',
            });
        },
    },
});
