/**
 * @sw-package discovery
 */

import { mount } from '@vue/test-utils';

async function createWrapper() {
    return mount(
        await wrapTestComponent('sw-channel-detail-analytics', {
            sync: true,
        }),
        {
            global: {
                stubs: {
                    'mt-card': {
                        template: '<div class="mt-card"><slot></slot></div>',
                    },
                    'sw-text-field': {
                        template: '<div class="sw-field sw-text-field"></div>',
                        props: ['disabled'],
                    },
                    'sw-container': {
                        template: '<div class="sw-container"><slot></slot></div>',
                        props: ['disabled'],
                    },
                },
                provide: {
                    repositoryFactory: {
                        create: () => ({
                            create: () => ({}),
                        }),
                    },
                },
            },
            props: {
                channel: {},
            },
        },
    );
}

describe('src/module/sw-channel/view/sw-channel-detail-analytics', () => {
    it('should have fields disabled when the user has no privileges', async () => {
        const wrapper = await createWrapper();

        const fields = wrapper.findAllComponents('.mt-switch');

        expect(fields.length).toBeGreaterThan(0);
        fields.forEach((field) => {
            expect(field.props('disabled')).toBe(true);
        });
    });

    it('should have fields enabled when the user has privileges', async () => {
        global.activeAclRoles = ['channel.editor'];

        const wrapper = await createWrapper();

        const fields = wrapper.findAllComponents('.mt-switch');

        expect(fields.length).toBeGreaterThan(0);
        fields.forEach((field) => {
            expect(field.props('disabled')).toBe(false);
        });
    });
});
