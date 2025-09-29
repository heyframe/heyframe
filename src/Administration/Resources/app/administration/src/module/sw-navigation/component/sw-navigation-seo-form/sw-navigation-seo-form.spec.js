/**
 * @sw-package discovery
 */
import { mount } from '@vue/test-utils';

async function createWrapper() {
    return mount(await wrapTestComponent('sw-navigation-seo-form', { sync: true }), {
        global: {
            stubs: {
                'sw-text-field': true,
                'mt-textarea': true,
            },
        },
        props: {
            navigation: {},
        },
    });
}

describe('src/module/sw-navigation/component/sw-navigation-seo-form', () => {
    beforeEach(() => {
        global.activeAclRoles = [];
    });

    it('should have an all fields enabled when having the right acl rights', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const wrapper = await createWrapper();

        const textFields = wrapper.findAll('sw-field-stub');

        textFields.forEach((textField) => {
            expect(textField.attributes().disabled).toBeUndefined();
        });
    });

    it('should have an all fields disabled when not having the right acl rights', async () => {
        const wrapper = await createWrapper();

        const textFields = wrapper.findAll('sw-field-stub');

        textFields.forEach((textField) => {
            expect(textField.attributes().disabled).toBe('true');
        });
    });
});
