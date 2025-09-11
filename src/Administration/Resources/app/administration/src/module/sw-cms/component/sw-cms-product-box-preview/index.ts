import template from './sw-cms-product-box-preview.html.twig';
import './sw-cms-product-box-preview.scss';

/**
 * @private
 * @sw-package discovery
 */
export default HeyFrame.Component.wrapComponentConfig({
    template,

    props: {
        hasText: {
            type: Boolean,
            required: false,
            default() {
                return false;
            },
        },
    },

    computed: {
        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },
    },
});
