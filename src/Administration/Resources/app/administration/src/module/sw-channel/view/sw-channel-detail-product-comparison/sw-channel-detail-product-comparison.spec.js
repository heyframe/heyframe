/**
 * @sw-package discovery
 */

import { mount } from '@vue/test-utils';

async function createWrapper() {
    return mount(
        await wrapTestComponent('sw-channel-detail-product-comparison', {
            sync: true,
        }),
        {
            global: {
                stubs: {
                    'mt-card': {
                        template: '<div class="mt-card"><slot></slot></div>',
                    },
                    'sw-code-editor': {
                        template: '<div class="sw-code-editor"></div>',
                        props: ['disabled'],
                    },
                    'sw-container': {
                        template: '<div class="sw-container"><slot></slot></div>',
                    },
                    'sw-button-process': true,
                    'sw-channel-detail-product-comparison-preview': true,
                },
                provide: {
                    channelService: {},
                    productExportService: {},
                    entityMappingService: {},
                    repositoryFactory: {},
                },
            },
            props: {
                productExport: {},
                channel: {},
            },
        },
    );
}

describe('src/module/sw-channel/view/sw-channel-detail-product-comparison', () => {
    beforeEach(() => {
        global.activeAclRoles = [];
    });

    it('should have codeEditors disabled when the user has no privileges', async () => {
        const wrapper = await createWrapper();

        const codeEditors = wrapper.findAllComponents('.sw-code-editor');

        expect(codeEditors.length).toBeGreaterThan(0);
        codeEditors.forEach((codeEditor) => {
            expect(codeEditor.props('disabled')).toBe(true);
        });
    });

    it('should have codeEditors enabled when the user has privileges', async () => {
        global.activeAclRoles = [
            'channel.editor',
        ];

        const wrapper = await createWrapper();

        const codeEditors = wrapper.findAllComponents('.sw-code-editor');

        expect(codeEditors.length).toBeGreaterThan(0);
        codeEditors.forEach((codeEditor) => {
            expect(codeEditor.attributes().disabled).toBeUndefined();
        });
    });
});
