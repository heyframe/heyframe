/**
 * @sw-package discovery
 */

import template from './sw-channel-modal-detail.html.twig';
import './sw-channel-modal-detail.scss';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default {
    template,

    props: {
        detailType: {
            type: Object,
            required: false,
            default: null,
        },
    },
};
