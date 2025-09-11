/**
 * @sw-package framework
 */

import AclService from 'src/app/service/acl.service';

describe('src/app/service/acl.service.ts', () => {
    beforeEach(() => {
        HeyFrame.Application.view = {};
        HeyFrame.Application.view.root = {};
        HeyFrame.Application.view.root.$router = {};
        HeyFrame.Application.view.root.$router.resolve = () => ({});
        HeyFrame.Store.get('settingsItems').settingsGroups.shop = [];
        HeyFrame.Store.get('settingsItems').settingsGroups.system = [];
    });

    it('should be an admin', async () => {
        HeyFrame.Store.get('session').setCurrentUser({ admin: true });
        const aclService = new AclService();

        expect(aclService.isAdmin()).toBe(true);
    });

    it('should not be an admin', async () => {
        HeyFrame.Store.get('session').setCurrentUser({ admin: false });
        const aclService = new AclService();

        expect(aclService.isAdmin()).toBe(false);
    });

    it('should not be an admin if the store is empty', async () => {
        HeyFrame.Store.get('session').removeCurrentUser();
        const aclService = new AclService();

        expect(aclService.isAdmin()).toBe(false);
    });

    it('should allow every privilege as an admin', async () => {
        HeyFrame.Store.get('session').setCurrentUser({ admin: true });
        const aclService = new AclService();

        expect(aclService.can('system.clear_cache')).toBe(true);
    });

    it('should disallow when privilege does not exist', async () => {
        HeyFrame.Store.get('session').setCurrentUser({ admin: false });
        const aclService = new AclService();

        expect(aclService.can('system.clear_cache')).toBeFalsy();
    });

    it('should allow when privilege exists', async () => {
        const aclService = new AclService();
        HeyFrame.Store.get('session').setCurrentUser({ admin: false, aclRoles: [{ privileges: ['system.clear_cache'] }] });

        expect(aclService.can('system.clear_cache')).toBe(true);
    });

    it('should return all privileges', async () => {
        HeyFrame.Store.get('session').setCurrentUser({
            admin: false,
            aclRoles: [
                {
                    privileges: [
                        'system.clear_cache',
                        'orders.create_discounts',
                    ],
                },
            ],
        });
        const aclService = new AclService();

        expect(aclService.privileges).toContain('system.clear_cache');
        expect(aclService.privileges).toContain('orders.create_discounts');
    });

    it('should return true if router is undefined', async () => {
        HeyFrame.Application.view.root.$router = null;
        HeyFrame.Store.get('session').setCurrentUser({ admin: false, aclRoles: [{ privileges: ['product.viewer'] }] });
        const aclService = new AclService();

        expect(aclService.hasAccessToRoute('sw.product.index')).toBe(true);
    });

    it('should have access to the route when no privilege exists', async () => {
        HeyFrame.Application.view.root.$router.resolve = () => ({});
        HeyFrame.Store.get('session').setCurrentUser({ admin: false, aclRoles: [{ privileges: ['product.viewer'] }] });
        const aclService = new AclService();

        expect(aclService.hasAccessToRoute('sw.product.index')).toBe(true);
    });

    it('should not have access to the route when privilege not matches', async () => {
        HeyFrame.Application.view.root.$router.resolve = () => ({
            meta: {
                privilege: 'category.viewer',
            },
        });
        HeyFrame.Store.get('session').setCurrentUser({ admin: false, aclRoles: [{ privileges: ['product.viewer'] }] });
        const aclService = new AclService();

        expect(aclService.hasAccessToRoute('sw.product.index')).toBeFalsy();
    });

    it('should have access to the route when privilege matches', async () => {
        HeyFrame.Application.view.root.$router.resolve = () => ({
            meta: {
                privilege: 'product.viewer',
            },
        });
        HeyFrame.Store.get('session').setCurrentUser({ admin: false, aclRoles: [{ privileges: ['product.viewer'] }] });
        const aclService = new AclService();

        expect(aclService.hasAccessToRoute('sw.product.index')).toBe(true);
    });

    it('should have access to the settings route when user has any access to settings', async () => {
        HeyFrame.Store.get('settingsItems').settingsGroups.shop = [
            {
                group: 'shop',
                icon: 'default-chart-pie',
                id: 'sw-settings-tax',
                label: 'sw-settings-tax.general.mainMenuItemGeneral',
                name: 'settings-tax',
                privilege: 'tax.viewer',
                to: 'sw.settings.tax.index',
            },
        ];
        HeyFrame.Store.get('session').setCurrentUser({ admin: false, aclRoles: [{ privileges: ['tax.viewer'] }] });
        const aclService = new AclService();

        expect(aclService.hasAccessToRoute('.sw.settings.index')).toBe(true);
        expect(aclService.hasAccessToRoute('/sw/settings/index')).toBe(true);
    });

    it('should have access to the settings route when user has no access to settings', async () => {
        HeyFrame.Store.get('settingsItems').settingsGroups.shop = [
            {
                group: 'shop',
                icon: 'default-chart-pie',
                id: 'sw-settings-tax',
                label: 'sw-settings-tax.general.mainMenuItemGeneral',
                name: 'settings-tax',
                privilege: 'tax.viewer',
                to: 'sw.settings.tax.index',
            },
        ];
        HeyFrame.Store.get('settingsItems').settingsGroups.system = [];
        HeyFrame.Store.get('session').setCurrentUser({ admin: false });
        const aclService = new AclService();

        expect(aclService.hasAccessToRoute('.sw.settings.index')).toBe(false);
        expect(aclService.hasAccessToRoute('/sw/settings/index')).toBe(false);
    });
});
