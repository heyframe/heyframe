/**
 * @package discovery
 */

import './mixin/sw-theme.mixin';
import './page/sw-theme-manager-detail';
import './page/sw-theme-manager-list';
import './component/sw-theme-list-item/';
import './component/sw-theme-modal/';
import './acl';

const { Module } = HeyFrame;

Module.register('sw-theme-manager', {
    type: 'core',
    title: 'sw-theme-manager.general.mainMenuItemGeneral',
    description: 'sw-theme-manager.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#ff68b4',
    icon: 'regular-content',
    favicon: 'icon-module-content.png',
    entity: 'theme',

    routes: {
        index: {
            component: 'sw-theme-manager-list',
            path: 'index',
            meta: {
                privilege: 'theme.viewer'
            }
        },
        detail: {
            component: 'sw-theme-manager-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sw.theme.manager.index',
                privilege: 'theme.viewer'
            }
        }
    },

    navigation: [{
        id: 'sw-theme-manager',
        label: 'sw-theme-manager.general.mainMenuItemGeneral',
        color: '#ff68b4',
        icon: 'default-object-image',
        path: 'sw.theme.manager.index',
        privilege: 'theme.viewer',
        position: 80,
        parent: 'sw-content'
    }],

    // Add theme route to channel
    routeMiddleware(next, currentRoute) {
        const name = 'sw.sales.channel.detail.theme';
        if (
            currentRoute.name === 'sw.sales.channel.detail'
            && currentRoute.children.every(child => child.name !== name)
        ) {
            currentRoute.children.push({
                component: 'sw-channel-detail-theme',
                name,
                isChildren: true,
                path: '/sw/sales/channel/detail/:id/theme',
                meta: {
                    parentPath: 'sw.sales.channel.list',
                    privilege: 'channel.viewer'
                }
            });
        }

        next(currentRoute);
    }
});
