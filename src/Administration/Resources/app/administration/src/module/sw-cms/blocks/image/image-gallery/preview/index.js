import template from './sw-cms-preview-image-gallery.html.twig';
import './sw-cms-preview-image-gallery.scss';

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
