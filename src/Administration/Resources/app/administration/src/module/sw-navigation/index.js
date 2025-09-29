/**
 * @sw-package discovery
 */
import './acl';
import defaultSearchConfiguration from './default-search-configuration';
import './page/sw-navigation-detail/store';

const { Module } = HeyFrame;

/* eslint-disable max-len, sw-deprecation-rules/private-feature-declarations */
HeyFrame.Component.register('sw-navigation-tree', () => import('./component/sw-navigation-tree'));
HeyFrame.Component.register('sw-landing-page-tree', () => import('./component/sw-landing-page-tree'));
HeyFrame.Component.register('sw-landing-page-view', () => import('./component/sw-landing-page-view'));
HeyFrame.Component.register('sw-navigation-view', () => import('./component/sw-navigation-view'));
HeyFrame.Component.register('sw-navigation-link-settings', () => import('./component/sw-navigation-link-settings'));
HeyFrame.Component.register('sw-navigation-layout-card', () => import('./component/sw-navigation-layout-card'));
HeyFrame.Component.register('sw-navigation-detail-menu', () => import('./component/sw-navigation-detail-menu'));
HeyFrame.Component.register('sw-navigation-seo-form', () => import('./component/sw-navigation-seo-form'));
HeyFrame.Component.register('sw-navigation-entry-point-card', () => import('./component/sw-navigation-entry-point-card'));
HeyFrame.Component.register('sw-navigation-entry-point-modal', () => import('./component/sw-navigation-entry-point-modal'));
HeyFrame.Component.register(
    'sw-navigation-entry-point-overwrite-modal',
    () => import('./component/sw-navigation-entry-point-overwrite-modal'),
);
HeyFrame.Component.extend(
    'sw-navigation-channel-multi-select',
    'sw-entity-multi-select',
    () => import('./component/sw-navigation-channel-multi-select'),
);
HeyFrame.Component.register('sw-navigation-detail', () => import('./page/sw-navigation-detail'));
HeyFrame.Component.register('sw-navigation-detail-base', () => import('./view/sw-navigation-detail-base'));
HeyFrame.Component.register('sw-navigation-detail-cms', () => import('./view/sw-navigation-detail-cms'));
HeyFrame.Component.register('sw-navigation-detail-custom-entity', () => import('./view/sw-navigation-detail-custom-entity'));
HeyFrame.Component.register('sw-landing-page-detail-base', () => import('./view/sw-landing-page-detail-base'));
HeyFrame.Component.register('sw-landing-page-detail-cms', () => import('./view/sw-landing-page-detail-cms'));
HeyFrame.Component.register('sw-navigation-detail-products', () => import('./view/sw-navigation-detail-products'));
HeyFrame.Component.register('sw-navigation-detail-seo', () => import('./view/sw-navigation-detail-seo'));
/* eslint-enable max-len, sw-deprecation-rules/private-feature-declarations */

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-navigation', {
    type: 'core',
    name: 'navigation',
    title: 'sw-navigation.general.mainMenuItemIndex',
    description: 'sw-navigation.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#57D9A3',
    icon: 'regular-products',
    favicon: 'icon-module-products.png',
    entity: 'navigation',

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
                route: manifest.routes.index,
                privilege: manifest.routes.index?.meta.privilege,
            },
            {
                name: manifest.name,
                icon: manifest.icon,
                color: manifest.color,
                route: {
                    ...manifest.routes.landingPageDetail,
                    params: { id: 'create' },
                },
                entity: 'landing_page',
                privilege: manifest.routes.landingPageDetail?.meta.privilege,
                action: true,
            },
        ];
    },

    routes: {
        index: {
            component: 'sw-navigation-detail',
            path: 'index',
            meta: {
                parentPath: 'sw.navigation.index',
                privilege: 'navigation.viewer',
            },
        },

        detail: {
            component: 'sw-navigation-detail',
            path: 'index/:id',
            meta: {
                privilege: 'navigation.viewer',
                appSystem: {
                    view: 'detail',
                },
            },
            redirect: {
                name: 'sw.navigation.detail.base',
            },

            children: {
                base: {
                    component: 'sw-navigation-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'sw.navigation.index',
                        privilege: 'navigation.viewer',
                    },
                },
                cms: {
                    component: 'sw-navigation-detail-cms',
                    path: 'cms',
                    meta: {
                        parentPath: 'sw.navigation.index',
                        privilege: 'navigation.viewer', // change in NEXT-8921 to CMS rights
                    },
                },
                products: {
                    component: 'sw-navigation-detail-products',
                    path: 'products',
                    meta: {
                        parentPath: 'sw.navigation.index',
                        privilege: 'navigation.viewer',
                    },
                },
                customEntity: {
                    component: 'sw-navigation-detail-custom-entity',
                    path: 'customEntity',
                    meta: {
                        parentPath: 'sw.navigation.index',
                        privilege: 'navigation.viewer',
                    },
                },
                seo: {
                    component: 'sw-navigation-detail-seo',
                    path: 'seo',
                    meta: {
                        parentPath: 'sw.navigation.index',
                        privilege: 'navigation.viewer',
                    },
                },
            },

            props: {
                default(route) {
                    return {
                        navigationId: route.params.id.toLowerCase(),
                    };
                },
            },
        },

        landingPageDetail: {
            component: 'sw-navigation-detail',
            path: 'landingPage/:id',
            meta: {
                privilege: 'navigation.viewer',
            },
            redirect: {
                name: 'sw.navigation.landingPageDetail.base',
            },

            children: {
                base: {
                    component: 'sw-landing-page-detail-base',
                    path: 'base',
                    meta: {
                        parentPath: 'sw.navigation.index',
                        privilege: 'navigation.viewer',
                    },
                },
                cms: {
                    component: 'sw-landing-page-detail-cms',
                    path: 'cms',
                    meta: {
                        parentPath: 'sw.navigation.index',
                        privilege: 'navigation.viewer', // change in NEXT-8921 to CMS rights
                    },
                },
            },

            props: {
                default(route) {
                    return {
                        landingPageId: route.params.id.toLowerCase(),
                    };
                },
            },
        },
    },

    navigation: [
        {
            id: 'sw-navigation',
            path: 'sw.navigation.index',
            label: 'sw-navigation.general.mainMenuItemIndex',
            parent: 'sw-content',
            privilege: 'navigation.viewer',
            position: 20,
        },
    ],

    defaultSearchConfiguration,
});
