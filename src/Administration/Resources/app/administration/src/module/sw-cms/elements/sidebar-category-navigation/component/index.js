import template from './sw-cms-el-category-navigation.html.twig';
import './sw-cms-el-category-navigation.scss';

/**
 * @private
 * @sw-package discovery
 */
export default {
    template,

    mixins: [
        HeyFrame.Mixin.getByName('cms-element'),
        HeyFrame.Mixin.getByName('placeholder'),
    ],

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.initElementConfig('category-navigation');
        },
    },
};
