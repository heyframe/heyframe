/**
 * @sw-package fundamentals@framework
 */
import './acl';

/* eslint-disable max-len, sw-deprecation-rules/private-feature-declarations */
HeyFrame.Component.register('sw-users-permissions', () => import('./page/sw-users-permissions'));
HeyFrame.Component.register(
    'sw-users-permissions-user-listing',
    () => import('./components/sw-users-permissions-user-listing'),
);
HeyFrame.Component.register(
    'sw-users-permissions-role-listing',
    () => import('./components/sw-users-permissions-role-listing'),
);
HeyFrame.Component.register(
    'sw-users-permissions-configuration',
    () => import('./components/sw-users-permissions-configuration'),
);
HeyFrame.Component.register(
    'sw-users-permissions-additional-permissions',
    () => import('./components/sw-users-permissions-additional-permissions'),
);
HeyFrame.Component.register(
    'sw-users-permissions-permissions-grid',
    () => import('./components/sw-users-permissions-permissions-grid'),
);
HeyFrame.Component.register(
    'sw-users-permissions-detailed-permissions-grid',
    () => import('./components/sw-users-permissions-detailed-permissions-grid'),
);
HeyFrame.Component.register(
    'sw-users-permissions-detailed-additional-permissions',
    () => import('./components/sw-users-permissions-detailed-additional-permissions'),
);
HeyFrame.Component.register('sw-users-permissions-user-detail', () => import('./page/sw-users-permissions-user-detail'));
HeyFrame.Component.extend(
    'sw-users-permissions-user-create',
    'sw-users-permissions-user-detail',
    () => import('./page/sw-users-permissions-user-create'),
);
HeyFrame.Component.register('sw-users-permissions-role-detail', () => import('./page/sw-users-permissions-role-detail'));
HeyFrame.Component.register(
    'sw-users-permissions-role-view-general',
    () => import('./view/sw-users-permissions-role-view-general'),
);
HeyFrame.Component.register(
    'sw-users-permissions-role-view-detailed',
    () => import('./view/sw-users-permissions-role-view-detailed'),
);
HeyFrame.Component.register(
    'sw-sso-users-permission-user-detail',
    () => import('./page/sw-sso-users-permission-user-detail'),
);
HeyFrame.Component.register('sw-user-sso-invitation-modal', () => import('./components/sw-user-sso-invitation-modal'));
HeyFrame.Component.register('sw-user-sso-status-label', () => import('./components/sw-user-sso-status-label'));
HeyFrame.Component.register(
    'sw-user-sso-access-key-create-modal',
    () => import('./components/sw-user-sso-access-key-create-modal'),
);

/* eslint-enable max-len, sw-deprecation-rules/private-feature-declarations */

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Module.register('sw-users-permissions', {
    type: 'core',
    name: 'users-permissions',
    title: 'sw-users-permissions.general.label',
    description: 'sw-users-permissions.general.label',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'regular-cog',
    favicon: 'icon-module-settings.png',
    entity: 'user',

    routes: {
        index: {
            component: 'sw-users-permissions',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index.system',
                privilege: 'users_and_permissions.viewer',
            },
        },
        'user.detail': {
            component: 'sw-users-permissions-user-detail',
            path: 'user.detail/:id?',
            meta: {
                parentPath: 'sw.users.permissions.index',
                privilege: 'users_and_permissions.viewer',
            },
        },
        'user.sso.detail': {
            component: 'sw-sso-users-permission-user-detail',
            path: 'user.sso.detail/:id?',
            meta: {
                parentPath: 'sw.users.permissions.index',
                privilege: 'users_and_permissions.viewer',
            },
        },
        'user.create': {
            component: 'sw-users-permissions-user-create',
            path: 'user.create',
            meta: {
                parentPath: 'sw.users.permissions.index',
                privilege: 'users_and_permissions.creator',
            },
        },
        'role.detail': {
            component: 'sw-users-permissions-role-detail',
            path: 'role.detail/:id?',
            meta: {
                parentPath: 'sw.users.permissions.index',
                privilege: 'users_and_permissions.viewer',
            },
            redirect: {
                name: 'sw.users.permissions.role.detail.general',
            },
            children: {
                general: {
                    component: 'sw-users-permissions-role-view-general',
                    path: 'general',
                    meta: {
                        parentPath: 'sw.users.permissions.index',
                        privilege: 'users_and_permissions.viewer',
                    },
                },
                'detailed-privileges': {
                    component: 'sw-users-permissions-role-view-detailed',
                    path: 'detailed-privileges',
                    meta: {
                        parentPath: 'sw.users.permissions.index',
                        privilege: 'users_and_permissions.viewer',
                    },
                },
            },
        },
    },

    settingsItem: {
        group: 'system',
        to: 'sw.users.permissions.index',
        icon: 'regular-user',
        privilege: 'users_and_permissions.viewer',
    },
});
