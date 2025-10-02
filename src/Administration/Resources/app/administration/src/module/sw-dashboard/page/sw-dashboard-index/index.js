import template from './sw-dashboard-index.html.twig';
import './sw-dashboard-index.scss';

/**
 * @sw-package after-sales
 *
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    template,

    data() {
        return {

        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },
});
