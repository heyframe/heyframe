/**
 * @sw-package discovery
 */
import { mount } from '@vue/test-utils';

async function createWrapper({ mediaRepositoryMock = undefined } = {}) {
    const repositorySpy = jest.fn(() => Promise.resolve(mediaRepositoryMock));
    const wrapper = mount(await wrapTestComponent('sw-navigation-detail-menu', { sync: true }), {
        global: {
            stubs: {
                'mt-card': {
                    template: '<div class="mt-card"><slot></slot></div>',
                },
                'sw-upload-listener': true,
                'sw-media-upload-v2': {
                    template: '<div class="sw-media-upload-v2"></div>',
                    props: ['disabled'],
                },
                'sw-text-editor': {
                    template: '<div class="sw-text-editor"></div>',
                    props: ['disabled'],
                },
                'sw-media-modal-v2': {
                    template: '<div class="sw-media-modal-v2"><button @click="onEmitSelection">Add media</button></div>',
                    methods: {
                        onEmitSelection() {
                            this.$emit('media-modal-selection-change', [
                                { id: 'id' },
                            ]);
                        },
                    },
                },
            },
            provide: {
                repositoryFactory: {
                    create: () => {
                        return {
                            get: repositorySpy,
                            search: () => Promise.resolve({}),
                        };
                    },
                },
            },
        },
        props: {
            navigation: {
                id: 'id',
                visible: true,
                getEntityName: () => {},
            },
        },
    });
    return { wrapper, repositorySpy };
}

describe('src/module/sw-navigation/component/sw-navigation-detail-menu', () => {
    beforeEach(() => {
        global.activeAclRoles = [];
    });

    it('should enable the visibility switch field when the acl privilege is missing', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const { wrapper } = await createWrapper();

        const switchField = wrapper.getComponent('.mt-switch');

        expect(switchField.props('disabled')).toBe(false);
    });

    it('should disable the visibility switch field when the acl privilege is missing', async () => {
        const { wrapper } = await createWrapper();

        const switchField = wrapper.getComponent('.mt-switch');

        expect(switchField.props('disabled')).toBe(true);
    });

    it('should enable the media upload', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const { wrapper } = await createWrapper();

        const mediaUpload = wrapper.getComponent('.sw-media-upload-v2');

        expect(mediaUpload.props('disabled')).toBe(false);
    });

    it('should disable the media upload', async () => {
        const { wrapper } = await createWrapper();

        const mediaUpload = wrapper.getComponent('.sw-media-upload-v2');

        expect(mediaUpload.props('disabled')).toBe(true);
    });

    it('should enable the text editor for the description', async () => {
        global.activeAclRoles = ['navigation.editor'];

        const { wrapper } = await createWrapper();

        const textEditor = wrapper.getComponent('.sw-text-editor');

        expect(textEditor.props('disabled')).toBe(false);
    });

    it('should disable the text editor for the description', async () => {
        const { wrapper } = await createWrapper();

        const textEditor = wrapper.getComponent('.sw-text-editor');

        expect(textEditor.props('disabled')).toBe(true);
    });

    it('should open media modal', async () => {
        const { wrapper } = await createWrapper();

        await wrapper.setData({ showMediaModal: true });

        const mediaModal = wrapper.find('.sw-media-modal-v2');

        expect(mediaModal.exists()).toBe(true);
    });

    it('should turn off media modal', async () => {
        const { wrapper } = await createWrapper();

        const mediaModal = wrapper.find('.sw-media-modal-v2');

        expect(mediaModal.exists()).toBeFalsy();
    });

    it('should be able to change navigation media', async () => {
        const { wrapper, repositorySpy } = await createWrapper({ mediaRepositoryMock: { id: 'id' } });

        await wrapper.setData({ showMediaModal: true });
        const button = wrapper.find('.sw-media-modal-v2 button');
        await button.trigger('click');

        expect(repositorySpy).toHaveBeenCalledWith('id');
        expect(wrapper.vm.navigation.mediaId).toBe('id');

        wrapper.vm.mediaRepository.get.mockRestore();
    });

    it('should not change navigation media when selected media is null', async () => {
        const { wrapper, repositorySpy } = await createWrapper();

        wrapper.vm.onMediaSelectionChange([]);

        expect(repositorySpy).not.toHaveBeenCalled();

        repositorySpy.mockRestore();
    });
});
