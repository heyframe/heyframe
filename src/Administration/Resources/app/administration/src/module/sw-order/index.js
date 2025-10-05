import './acl';

import './mixin/cart-notification.mixin';

import defaultSearchConfiguration from './default-search-configuration';

/**
 * @sw-package checkout
 */

const { Module } = HeyFrame;

/* eslint-disable max-len, sw-deprecation-rules/private-feature-declarations */
HeyFrame.Component.register('sw-order-list', () => import('./page/sw-order-list'));
HeyFrame.Component.register('sw-order-detail', () => import('./page/sw-order-detail'));
HeyFrame.Component.register('sw-order-create', () => import('./page/sw-order-create'));
HeyFrame.Component.register('sw-order-detail-general', () => import('./view/sw-order-detail-general'));
HeyFrame.Component.register('sw-order-detail-details', () => import('./view/sw-order-detail-details'));
HeyFrame.Component.register('sw-order-detail-documents', () => import('./view/sw-order-detail-documents'));
HeyFrame.Component.register('sw-order-create-base', () => import('./view/sw-order-create-base'));
HeyFrame.Component.register('sw-order-create-initial', () => import('./view/sw-order-create-initial'));
HeyFrame.Component.register('sw-order-create-general', () => import('./view/sw-order-create-general'));
HeyFrame.Component.register('sw-order-create-details', () => import('./view/sw-order-create-details'));
HeyFrame.Component.register(
    'sw-order-nested-line-items-modal',
    () => import('./component/sw-order-nested-line-items-modal'),
);
HeyFrame.Component.register('sw-order-nested-line-items-row', () => import('./component/sw-order-nested-line-items-row'));
HeyFrame.Component.register('sw-order-line-items-grid', () => import('./component/sw-order-line-items-grid'));
HeyFrame.Component.register(
    'sw-order-line-items-grid-channel',
    () => import('./component/sw-order-line-items-grid-channel'),
);
HeyFrame.Component.register('sw-order-delivery-metadata', () => import('./component/sw-order-delivery-metadata'));
HeyFrame.Component.register('sw-order-customer-comment', () => import('./component/sw-order-customer-comment'));
HeyFrame.Component.register('sw-order-product-select', () => import('./component/sw-order-product-select'));
HeyFrame.Component.register('sw-order-saveable-field', () => import('./component/sw-order-saveable-field'));
HeyFrame.Component.register('sw-order-address-modal', () => import('./component/sw-order-address-modal'));
HeyFrame.Component.register('sw-order-address-selection', () => import('./component/sw-order-address-selection'));
HeyFrame.Component.register('sw-order-leave-page-modal', () => import('./component/sw-order-leave-page-modal'));
HeyFrame.Component.register(
    'sw-order-save-changes-beforehand-modal',
    () => import('./component/sw-order-save-changes-beforehand-modal'),
);
HeyFrame.Component.register(
    'sw-order-state-change-modal-attach-documents',
    () => import('./component/sw-order-state-change-modal/sw-order-state-change-modal-attach-documents'),
);
HeyFrame.Component.register('sw-order-state-history-card', () => import('./component/sw-order-state-history-card'));
HeyFrame.Component.register(
    'sw-order-state-history-card-entry',
    () => import('./component/sw-order-state-history-card-entry'),
);
HeyFrame.Component.register('sw-order-state-history-modal', () => import('./component/sw-order-state-history-modal'));
HeyFrame.Component.register('sw-order-state-change-modal', () => import('./component/sw-order-state-change-modal'));
HeyFrame.Component.register('sw-order-state-select-v2', () => import('./component/sw-order-state-select-v2'));
HeyFrame.Component.register('sw-order-details-state-card', () => import('./component/sw-order-details-state-card'));
HeyFrame.Component.register('sw-order-inline-field', () => import('./component/sw-order-inline-field'));

/**
 * @deprecated tag:v6.8.0 - File will be removed. No longer used.
 */
HeyFrame.Component.register('sw-order-user-card', () => import('./component/sw-order-user-card'));
HeyFrame.Component.register('sw-order-create-details-header', () => import('./component/sw-order-create-details-header'));
HeyFrame.Component.register('sw-order-create-details-body', () => import('./component/sw-order-create-details-body'));
HeyFrame.Component.register('sw-order-create-details-footer', () => import('./component/sw-order-create-details-footer'));
HeyFrame.Component.register('sw-order-create-address-modal', () => import('./component/sw-order-create-address-modal'));
HeyFrame.Component.register('sw-order-new-customer-modal', () => import('./component/sw-order-new-customer-modal'));
HeyFrame.Component.register('sw-order-promotion-field', () => import('./component/sw-order-promotion-field'));
HeyFrame.Component.extend(
    'sw-order-promotion-tag-field',
    'sw-tagged-field',
    () => import('./component/sw-order-promotion-tag-field'),
);
HeyFrame.Component.register(
    'sw-order-create-invalid-promotion-modal',
    () => import('./component/sw-order-create-invalid-promotion-modal'),
);
HeyFrame.Component.register('sw-order-create-promotion-modal', () => import('./component/sw-order-create-promotion-modal'));
HeyFrame.Component.register('sw-order-create-general-info', () => import('./component/sw-order-create-general-info'));
HeyFrame.Component.register(
    'sw-order-select-document-type-modal',
    () => import('./component/sw-order-select-document-type-modal'),
);
HeyFrame.Component.register('sw-order-general-info', () => import('./component/sw-order-general-info'));
HeyFrame.Component.register('sw-order-send-document-modal', () => import('./component/sw-order-send-document-modal'));
HeyFrame.Component.register('sw-order-create-initial-modal', () => import('./component/sw-order-create-initial-modal'));
HeyFrame.Component.register('sw-order-customer-grid', () => import('./component/sw-order-customer-grid'));
HeyFrame.Component.register('sw-order-create-options', () => import('./component/sw-order-create-options'));
HeyFrame.Component.register(
    'sw-order-customer-address-select',
    () => import('./component/sw-order-customer-address-select'),
);
/* eslint-enable max-len, sw-deprecation-rules/private-feature-declarations */

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-order', {
    type: 'core',
    name: 'order',
    title: 'sw-order.general.mainMenuItemGeneral',
    description: 'sw-order.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#A092F0',
    icon: 'solid-shopping-bag',
    favicon: 'icon-module-orders.png',
    entity: 'order',

    routes: {
        index: {
            components: {
                default: 'sw-order-list',
            },
            path: 'index',
            meta: {
                privilege: 'order.viewer',
                appSystem: {
                    view: 'list',
                },
            },
        },

        create: {
            component: 'sw-order-create',
            path: 'create',
            redirect: {
                name: 'sw.order.create.initial',
            },
            meta: {
                privilege: 'order.creator',
            },
            children: orderCreateChildren(),
        },

        detail: {
            component: 'sw-order-detail',
            path: 'detail/:id',
            redirect: {
                name: 'sw.order.detail.general',
            },
            meta: {
                privilege: 'order.viewer',
                appSystem: {
                    view: 'detail',
                },
            },
            children: orderDetailChildren(),
            props: {
                default: ($route) => {
                    return { orderId: $route.params.id.toLowerCase() };
                },
            },
        },
    },

    navigation: [
        {
            id: 'sw-order',
            label: 'sw-order.general.mainMenuItemGeneral',
            color: '#A092F0',
            icon: 'regular-shopping-bag',
            position: 30,
            privilege: 'order.viewer',
        },
        {
            path: 'sw.order.index',
            label: 'sw-order.general.mainMenuItemList',
            parent: 'sw-order',
            privilege: 'order.viewer',
        },
    ],

    defaultSearchConfiguration,
});

function orderDetailChildren() {
    return {
        general: {
            component: 'sw-order-detail-general',
            path: 'general',
            meta: {
                parentPath: 'sw.order.index',
                privilege: 'order.viewer',
            },
        },
        details: {
            component: 'sw-order-detail-details',
            path: 'details',
            meta: {
                parentPath: 'sw.order.index',
                privilege: 'order.viewer',
            },
        },
        documents: {
            component: 'sw-order-detail-documents',
            path: 'documents',
            meta: {
                parentPath: 'sw.order.index',
                privilege: 'order.viewer',
            },
        },
    };
}

function orderCreateChildren() {
    return {
        initial: {
            component: 'sw-order-create-initial',
            path: 'initial',
            meta: {
                parentPath: 'sw.order.index',
                privilege: 'order.creator',
            },
        },
        general: {
            component: 'sw-order-create-general',
            path: 'general',
            meta: {
                parentPath: 'sw.order.index',
                privilege: 'order.creator',
            },
        },
        details: {
            component: 'sw-order-create-details',
            path: 'details',
            meta: {
                parentPath: 'sw.order.index',
                privilege: 'order.creator',
            },
        },
    };
}
