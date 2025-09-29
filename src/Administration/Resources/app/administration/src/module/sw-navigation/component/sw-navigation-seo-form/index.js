import template from './sw-navigation-seo-form.html.twig';

/**
 * @sw-package discovery
 */
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    inject: ['acl'],

    props: {
        navigation: {
            type: Object,
            required: true,
        },
    },
};
