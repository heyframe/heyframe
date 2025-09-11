/**
 * @sw-package discovery
 */

import './service/export-template.service';
import './product-export-templates';
import './service/domain-link.service';
import './service/channel-favorites.service';
import './component/structure/sw-admin-menu-extension';
import './acl';

import defaultSearchConfiguration from './default-search-configuration';

const { Module } = HeyFrame;

/* eslint-disable max-len, sw-deprecation-rules/private-feature-declarations */
HeyFrame.Component.register(
    'sw-channel-defaults-select',
    () => import('./component/sw-channel-defaults-select'),
);
HeyFrame.Component.register('sw-channel-modal', () => import('./component/sw-channel-modal'));
HeyFrame.Component.register('sw-channel-modal-grid', () => import('./component/sw-channel-modal-grid'));
HeyFrame.Component.register('sw-channel-modal-detail', () => import('./component/sw-channel-modal-detail'));
HeyFrame.Component.register('sw-channel-detail-domains', () => import('./component/sw-channel-detail-domains'));
HeyFrame.Component.register(
    'sw-channel-detail-hreflang',
    () => import('./component/sw-channel-detail-hreflang'),
);
HeyFrame.Component.register('sw-channel-detail', () => import('./page/sw-channel-detail'));
HeyFrame.Component.extend(
    'sw-channel-create',
    'sw-channel-detail',
    () => import('./page/sw-channel-create'),
);
HeyFrame.Component.register('sw-channel-list', () => import('./page/sw-channel-list'));
HeyFrame.Component.register('sw-channel-detail-base', () => import('./view/sw-channel-detail-base'));
HeyFrame.Component.register('sw-channel-detail-products', () => import('./view/sw-channel-detail-products'));
HeyFrame.Component.register('sw-channel-detail-analytics', () => import('./view/sw-channel-detail-analytics'));
HeyFrame.Component.extend(
    'sw-channel-create-base',
    'sw-channel-detail-base',
    () => import('./view/sw-channel-create-base'),
);
HeyFrame.Component.register(
    'sw-channel-detail-product-comparison',
    () => import('./view/sw-channel-detail-product-comparison'),
);
HeyFrame.Component.register(
    'sw-channel-detail-product-comparison-preview',
    () => import('./view/sw-channel-detail-product-comparison-preview'),
);
HeyFrame.Component.register(
    'sw-channel-products-assignment-modal',
    () => import('./component/sw-channel-products-assignment-modal'),
);
HeyFrame.Component.register(
    'sw-channel-products-assignment-single-products',
    () => import('./component/sw-channel-products-assignment-single-products'),
);
HeyFrame.Component.register(
    'sw-channel-products-assignment-dynamic-product-groups',
    () => import('./component/sw-channel-products-assignment-dynamic-product-groups'),
);
HeyFrame.Component.register(
    'sw-channel-product-assignment-categories',
    () => import('./component/sw-channel-product-assignment-categories'),
);
HeyFrame.Component.register('sw-channel-menu', () => import('./component/structure/sw-channel-menu'));

HeyFrame.Component.register('sw-channel-measurement', () => import('./component/sw-channel-measurement'));
/* eslint-enable max-len, sw-deprecation-rules/private-feature-declarations */

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-channel', {
    type: 'core',
    name: 'channel',
    title: 'sw-channel.general.titleMenuItems',
    description: 'The module for managing Sales Channels.',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#14D7A5',
    icon: 'regular-server',
    entity: 'channel',

    searchMatcher: (regex, labelType, manifest) => {
        const match = labelType.toLowerCase().match(regex);

        if (!match) {
            return false;
        }

        return [
            {
                name: manifest.name,
                icon: manifest.icon,
                color: manifest.color,
                label: labelType,
                entity: manifest.entity,
                route: manifest.routes.list,
                privilege: manifest.routes.list?.meta.privilege,
            },
        ];
    },

    routes: {
        detail: {
            component: 'sw-channel-detail',
            path: 'detail/:id',
            meta: {
                parentPath: 'sw.channel.list',
                privilege: 'channel.viewer',
            },
            redirect: {
                name: 'sw.channel.detail.base',
            },
            children: {
                base: {
                    component: 'sw-channel-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'sw.channel.list',
                        privilege: 'channel.viewer',
                    },
                },
                products: {
                    component: 'sw-channel-detail-products',
                    path: 'products',
                    meta: {
                        parentPath: 'sw.channel.list',
                        privilege: 'channel.viewer',
                    },
                },
                productComparison: {
                    component: 'sw-channel-detail-product-comparison',
                    path: 'product-comparison',
                    meta: {
                        parentPath: 'sw.channel.list',
                        privilege: 'channel.viewer',
                    },
                },
                analytics: {
                    component: 'sw-channel-detail-analytics',
                    path: 'analytics',
                    meta: {
                        parentPath: 'sw.channel.list',
                        privilege: 'channel.viewer',
                    },
                },
            },
        },

        create: {
            component: 'sw-channel-create',
            path: 'create/:typeId',
            redirect: {
                name: 'sw.channel.create.base',
            },
            children: {
                base: {
                    component: 'sw-channel-create-base',
                    path: 'base',
                    meta: {
                        parentPath: 'sw.channel.list',
                        privilege: 'channel.creator',
                    },
                },
            },
        },

        list: {
            component: 'sw-channel-list',
            path: 'list',
            meta: {
                privilege: 'channel.viewer',
            },
        },
    },

    defaultSearchConfiguration,
});
