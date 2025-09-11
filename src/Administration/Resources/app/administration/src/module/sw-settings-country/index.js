/**
 * @sw-package fundamentals@discovery
 */

import './acl';

const { Module } = HeyFrame;

/* eslint-disable max-len, sw-deprecation-rules/private-feature-declarations */
HeyFrame.Component.register('sw-settings-country-list', () => import('./page/sw-settings-country-list'));
HeyFrame.Component.register('sw-settings-country-detail', () => import('./page/sw-settings-country-detail'));
HeyFrame.Component.extend(
    'sw-settings-country-create',
    'sw-settings-country-detail',
    () => import('./page/sw-settings-country-create'),
);
HeyFrame.Component.register('sw-country-state-detail', () => import('./component/sw-country-state-detail'));
HeyFrame.Component.register('sw-settings-country-general', () => import('./component/sw-settings-country-general'));
HeyFrame.Component.register('sw-settings-country-state', () => import('./component/sw-settings-country-state'));
HeyFrame.Component.register(
    'sw-settings-country-currency-dependent-modal',
    () => import('./component/sw-settings-country-currency-dependent-modal'),
);
HeyFrame.Component.register(
    'sw-settings-country-currency-hamburger-menu',
    () => import('./component/sw-settings-country-currency-hamburger-menu'),
);
HeyFrame.Component.register(
    'sw-settings-country-preview-template',
    () => import('./component/sw-settings-country-preview-template'),
);
HeyFrame.Component.register(
    'sw-settings-country-new-snippet-modal',
    () => import('./component/sw-settings-country-new-snippet-modal'),
);
HeyFrame.Component.register(
    'sw-settings-country-address-handling',
    () => import('./component/sw-settings-country-address-handling'),
);
HeyFrame.Component.register('sw-multi-snippet-drag-and-drop', () => import('./component/sw-multi-snippet-drag-and-drop'));
/* eslint-enable max-len, sw-deprecation-rules/private-feature-declarations */

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-settings-country', {
    type: 'core',
    name: 'settings-country',
    title: 'sw-settings-country.general.mainMenuItemGeneral',
    description: 'Country section in the settings module',
    color: '#9AA8B5',
    icon: 'regular-cog',
    favicon: 'icon-module-settings.png',
    entity: 'country',

    routes: {
        index: {
            component: 'sw-settings-country-list',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'country.viewer',
            },
        },
        detail: {
            component: 'sw-settings-country-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sw.settings.country.index',
                privileges: [
                    'country.viewer',
                    'country.editor',
                ],
            },

            redirect: {
                name: 'sw.settings.country.detail.general',
            },

            children: {
                general: {
                    component: 'sw-settings-country-general',
                    path: 'general',
                    meta: {
                        parentPath: 'sw.settings.country.index',
                        privileges: [
                            'country.editor',
                            'country.creator',
                        ],
                    },
                },

                state: {
                    component: 'sw-settings-country-state',
                    path: 'state',
                    meta: {
                        parentPath: 'sw.settings.country.index',
                        privileges: [
                            'country.editor',
                            'country.creator',
                        ],
                    },
                },

                'address-handling': {
                    component: 'sw-settings-country-address-handling',
                    path: 'address-handling',
                    meta: {
                        parentPath: 'sw.settings.country.index',
                        privileges: [
                            'country.editor',
                            'country.creator',
                        ],
                    },
                },
            },
        },
        create: {
            component: 'sw-settings-country-create',
            path: 'create',
            meta: {
                parentPath: 'sw.settings.country.index',
                privilege: 'country.creator',
            },

            redirect: {
                name: 'sw.settings.country.create.general',
            },

            children: {
                general: {
                    component: 'sw-settings-country-general',
                    path: 'general',
                    meta: {
                        parentPath: 'sw.settings.country.index',
                        privilege: 'country.creator',
                    },
                },

                state: {
                    component: 'sw-settings-country-state',
                    path: 'state',
                    meta: {
                        parentPath: 'sw.settings.country.index',
                        privilege: 'country.creator',
                    },
                },

                'address-handling': {
                    component: 'sw-settings-country-address-handling',
                    path: 'address-handling',
                    meta: {
                        parentPath: 'sw.settings.country.index',
                        privileges: 'country.creator',
                    },
                },
            },
        },
    },

    settingsItem: {
        group: 'localization',
        to: 'sw.settings.country.index',
        icon: 'regular-map',
        privilege: 'country.viewer',
    },
});
