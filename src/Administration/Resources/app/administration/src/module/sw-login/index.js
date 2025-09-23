/**
 * @sw-package framework
 */

import zh from './snippet/zh.json';
import en from './snippet/en.json';

const { Component, Module } = HeyFrame;

/** @private */
Component.register('sw-login-login', () => import('./view/sw-login-login'));
/** @private */
Component.register('sw-login', () => import('./page/index'));

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
Module.register('sw-login', {
    type: 'core',
    name: 'login',
    title: 'sw-login.general.mainMenuItemsGeneral',
    description: 'sw-login.general.description',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#F19D12',

    snippets: {
        'zh-CN': zh,
        'en-GB': en,
    },

    routes: {
        index: {
            component: 'sw-login',
            path: '/login',
            alias: '/signin',
            coreRoute: true,
            redirect: {
                name: 'sw.login.index.login',
            },
            props: {
                default: (route) => {
                    return {
                        hash: route.params.hash,
                    };
                },
            },
            children: {
                login: {
                    component: 'sw-login-login',
                    path: '',
                }
            },
        },
    },
});
