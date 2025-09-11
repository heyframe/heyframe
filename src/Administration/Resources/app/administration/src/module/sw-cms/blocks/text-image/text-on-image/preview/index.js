import template from './sw-cms-preview-text-on-image.html.twig';
import './sw-cms-preview-text-on-image.scss';

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
