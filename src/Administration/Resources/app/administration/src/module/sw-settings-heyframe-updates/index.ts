/**
 * @sw-package framework
 */

import './acl';

const { Component, Module } = HeyFrame;

/** @private */
Component.register(
    'sw-settings-heyframe-updates-requirements',
    () => import('./view/sw-settings-heyframe-updates-requirements'),
);
/** @private */
Component.register('sw-settings-heyframe-updates-plugins', () => import('./view/sw-settings-heyframe-updates-plugins'));
/** @private */
Component.register('sw-settings-heyframe-updates-info', () => import('./view/sw-settings-heyframe-updates-info'));
/** @private */
Component.register('sw-settings-heyframe-updates-index', () => import('./page/sw-settings-heyframe-updates-index'));
/** @private */
Component.register('sw-settings-heyframe-updates-wizard', () => import('./page/sw-settings-heyframe-updates-wizard'));

/**
 * @private
 */
Module.register('sw-settings-heyframe-updates', {
    type: 'core',
    name: 'settings-heyframe-updates',
    title: 'sw-settings-heyframe-updates.general.emptyTitle',
    description: 'sw-settings-heyframe-updates.general.emptyTitle',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'regular-cog',
    favicon: 'icon-module-settings.png',

    routes: {
        wizard: {
            component: 'sw-settings-heyframe-updates-wizard',
            path: 'wizard',
            meta: {
                parentPath: 'sw.settings.index.system',
                privilege: 'system.core_update',
            },
        },
    },

    settingsItem: {
        privilege: 'system.core_update',
        group: 'system',
        to: 'sw.settings.heyframe.updates.wizard',
        icon: 'regular-sync',
    },
});
