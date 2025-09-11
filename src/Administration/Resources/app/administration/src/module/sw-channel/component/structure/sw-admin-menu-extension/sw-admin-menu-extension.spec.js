/**
 * @sw-package discovery
 */

import { mount } from '@vue/test-utils';
import 'src/app/component/structure/sw-admin-menu';
import swAdminMenuExtension from 'src/module/sw-channel/component/structure/sw-admin-menu-extension';
import createMenuService from 'src/app/service/menu.service';

// Turn off known errors
import { missingGetListMethod } from 'test/_helper_/allowedErrors';

HeyFrame.Component.register('sw-admin-menu-extension', swAdminMenuExtension);

global.allowedErrors = [missingGetListMethod];

const menuService = createMenuService(HeyFrame.Module);
HeyFrame.Service().register('menuService', () => menuService);

async function createWrapper() {
    return mount(await wrapTestComponent('sw-admin-menu', { sync: true }), {
        global: {
            stubs: {
                'sw-version': true,
                'sw-loader': true,
                'sw-avatar': true,
                'sw-shortcut-overview': true,
                'sw-channel-menu': true,
                'sw-admin-menu-item': true,
            },
            provide: {
                loginService: {
                    notifyOnLoginListener: () => {},
                },
                userService: {
                    getUser: () => Promise.resolve({ data: {} }),
                },
                menuService,
                appModulesService: {
                    fetchAppModules: () => Promise.resolve([]),
                },
                customEntityDefinitionService: {
                    getMenuEntries: () => {
                        return [];
                    },
                },
            },
        },
    });
}

describe('module/sw-channel/component/structure/sw-admin-menu-extension', () => {
    beforeAll(() => {
        HeyFrame.Store.get('session').setCurrentUser({});
    });

    it('should not show the sw-channel-menu when privilege does not exist', async () => {
        global.activeAclRoles = [];
        const wrapper = await createWrapper();
        await wrapper.vm.$nextTick();
        const swChannelMenu = wrapper.find('sw-channel-menu-stub');

        expect(swChannelMenu.exists()).toBeFalsy();
    });

    it('should show the sw-channel-menu when privilege exists', async () => {
        global.activeAclRoles = ['channel.viewer'];
        const wrapper = await createWrapper();
        const swChannelMenu = wrapper.find('sw-channel-menu-stub');

        expect(swChannelMenu.exists()).toBeTruthy();
    });
});
