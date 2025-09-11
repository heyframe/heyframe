/**
 * @sw-package discovery
 */

import template from './sw-admin-menu-extension.html.twig';

const { Component } = HeyFrame;

Component.override('sw-admin-menu', {
    template,

    inject: ['acl'],

    computed: {
        canViewChannels() {
            return this.acl.can('channel.viewer');
        },
    },
});
