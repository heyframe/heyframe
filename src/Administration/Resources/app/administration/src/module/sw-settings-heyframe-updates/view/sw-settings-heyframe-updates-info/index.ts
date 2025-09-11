import './sw-heyframe-updates-info.scss';
import template from './sw-heyframe-updates-info.html.twig';

const { Component } = HeyFrame;

/**
 * @sw-package framework
 * @private
 */
export default Component.wrapComponentConfig({
    template,

    props: {
        changelog: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
        },
    },
});
