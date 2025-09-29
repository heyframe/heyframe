/**
 * @sw-package discovery
 */
import { mount } from '@vue/test-utils';

const navigationMock = {
    media: [],
    name: 'Computer parts',
    footerChannels: [],
    navigationChannels: [],
    serviceChannels: [],
    productAssignmentType: 'product',
    isNew: () => false,
};

async function createWrapper() {
    HeyFrame.Store.get('swCategoryDetail').$reset();
    HeyFrame.Store.get('swCategoryDetail').navigation = navigationMock;

    return mount(await wrapTestComponent('sw-navigation-detail-base', { sync: true }), {
        global: {
            stubs: {
                'mt-card': {
                    template: '<div class="mt-card"><slot></slot></div>',
                },
                'sw-container': {
                    template: '<div class="sw-container"><slot></slot></div>',
                },
                'sw-single-select': {
                    template: '<input type="select" class="sw-single-select"></input>',
                    props: ['disabled'],
                },
                'sw-entity-tag-select': {
                    template: '<input type="select" class="sw-entity-tag-select"></input>',
                    props: ['disabled'],
                },
                'sw-navigation-detail-menu': {
                    template: '<div class="sw-navigation-detail-menu"></div>',
                },
                'sw-navigation-entry-point-card': true,
                'sw-navigation-link-settings': true,
                'sw-custom-field-set-renderer': true,
            },
        },
        props: {
            isLoading: false,
            manualAssignedProductsCount: 0,
        },
    });
}

describe('module/sw-navigation/view/sw-navigation-detail-base.spec', () => {
    it('should disable all interactive elements', async () => {
        global.activeAclRoles = [];

        const wrapper = await createWrapper();

        wrapper.findAllComponents('input').forEach((element) => {
            expect(element.props('disabled')).toBe(true);
        });
    });

    it('should enable all interactive elements', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        wrapper.findAllComponents('input').forEach((element) => {
            expect(element.props('disabled')).toBe(false);
        });
    });
});
