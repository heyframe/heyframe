import template from './sw-extension-app-module-error-page.html.twig';
import './sw-extension-app-module-error-page.scss';

/**
 * @sw-package checkout
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    template,

    computed: {
        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },
    },

    methods: {
        goBack(): void {
            this.$router.go(-1);
        },
    },
});
