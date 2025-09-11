import template from './sw-cms-el-preview-image-slider.html.twig';
import './sw-cms-el-preview-image-slider.scss';

/**
 * @private
 * @sw-package discovery
 */
export default {
    template,

    computed: {
        assetFilter() {
            return HeyFrame.Filter.getByName('asset');
        },
    },
};
