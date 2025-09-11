/**
 * @sw-package framework
 */
import './acl';

const { Module } = HeyFrame;

/* eslint-disable max-len, sw-deprecation-rules/private-feature-declarations */
HeyFrame.Component.extend(
    'sw-settings-custom-field-set-create',
    'sw-settings-custom-field-set-detail',
    () => import('./page/sw-settings-custom-field-set-create'),
);
HeyFrame.Component.register('sw-settings-custom-field-set-list', () => import('./page/sw-settings-custom-field-set-list'));
HeyFrame.Component.register(
    'sw-settings-custom-field-set-detail',
    () => import('./page/sw-settings-custom-field-set-detail'),
);
HeyFrame.Component.register(
    'sw-custom-field-translated-labels',
    () => import('./component/sw-custom-field-translated-labels'),
);
HeyFrame.Component.register('sw-custom-field-set-detail-base', () => import('./component/sw-custom-field-set-detail-base'));
HeyFrame.Component.register('sw-custom-field-list', () => import('./component/sw-custom-field-list'));
HeyFrame.Component.register('sw-custom-field-detail', () => import('./component/sw-custom-field-detail'));
HeyFrame.Component.register('sw-custom-field-type-base', () => import('./component/sw-custom-field-type-base'));
HeyFrame.Component.extend(
    'sw-custom-field-type-select',
    'sw-custom-field-type-base',
    () => import('./component/sw-custom-field-type-select'),
);
HeyFrame.Component.extend(
    'sw-custom-field-type-entity',
    'sw-custom-field-type-select',
    () => import('./component/sw-custom-field-type-entity'),
);
HeyFrame.Component.extend(
    'sw-custom-field-type-text',
    'sw-custom-field-type-base',
    () => import('./component/sw-custom-field-type-text'),
);
HeyFrame.Component.extend(
    'sw-custom-field-type-number',
    'sw-custom-field-type-base',
    () => import('./component/sw-custom-field-type-number'),
);
HeyFrame.Component.extend(
    'sw-custom-field-type-date',
    'sw-custom-field-type-base',
    () => import('./component/sw-custom-field-type-date'),
);
HeyFrame.Component.extend(
    'sw-custom-field-type-checkbox',
    'sw-custom-field-type-base',
    () => import('./component/sw-custom-field-type-checkbox'),
);
HeyFrame.Component.extend(
    'sw-custom-field-type-text-editor',
    'sw-custom-field-type-base',
    () => import('./component/sw-custom-field-type-text-editor'),
);
/* eslint-enable max-len, sw-deprecation-rules/private-feature-declarations */

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-settings-custom-field', {
    type: 'core',
    name: 'settings-custom-field',
    title: 'sw-settings-custom-field.general.mainMenuItemGeneral',
    description: 'sw-settings-custom-field.general.description',
    color: '#9AA8B5',
    icon: 'regular-cog',
    favicon: 'icon-module-settings.png',
    entity: 'custom-field-set',

    routes: {
        index: {
            component: 'sw-settings-custom-field-set-list',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index.system',
                privilege: 'custom_field.viewer',
            },
        },
        detail: {
            component: 'sw-settings-custom-field-set-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sw.settings.custom.field.index',
                privilege: 'custom_field.viewer',
            },
        },
        create: {
            component: 'sw-settings-custom-field-set-create',
            path: 'create',
            meta: {
                parentPath: 'sw.settings.custom.field.index',
                privilege: 'custom_field.creator',
            },
        },
    },

    settingsItem: {
        group: 'content',
        to: 'sw.settings.custom.field.index',
        icon: 'regular-bars-square',
        privilege: 'custom_field.viewer',
    },
});
