/**
 * @sw-package framework
 */
import initState from 'src/app/init-pre/state.init';

describe('src/app/init-pre/state.init.ts', () => {
    initState();

    it('should contain all state methods', () => {
        expect(HeyFrame.State._store).toBeDefined();
        expect(HeyFrame.State.list).toBeDefined();
        expect(HeyFrame.State.get).toBeDefined();
        expect(HeyFrame.State.getters).toBeDefined();
        expect(HeyFrame.State.commit).toBeDefined();
        expect(HeyFrame.State.dispatch).toBeDefined();
        expect(HeyFrame.State.watch).toBeDefined();
        expect(HeyFrame.State.subscribe).toBeDefined();
        expect(HeyFrame.State.subscribeAction).toBeDefined();
        expect(HeyFrame.State.registerModule).toBeDefined();
        expect(HeyFrame.State.unregisterModule).toBeDefined();
    });

    it('should initialized all state modules', () => {
        expect(HeyFrame.Store.get('heyframeApps')).toBeDefined();
    });

    it('should be able to get cmsPageState backwards compatible', () => {
        // The cmsPageState is deprecated and causes a warning, therefore ignore it
        global.allowedErrors.push({
            method: 'warn',
            msgCheck: (_, msg) => {
                if (typeof msg !== 'string') {
                    return false;
                }

                return msg === 'HeyFrame.State.get("cmsPageState") is deprecated! Use HeyFrame.Store.get instead.';
            },
        });

        HeyFrame.Store.register({
            id: 'cmsPage',
            state: () => ({
                foo: 'bar',
            }),
        });

        expect(HeyFrame.Store.get('cmsPage').foo).toBe('bar');
        HeyFrame.Store.unregister('cmsPage');
    });

    it('should be able to commit cmsPageState backwards compatible', () => {
        // The cmsPageState is deprecated and causes a warning, therefore ignore it
        global.allowedErrors.push({
            method: 'warn',
            msgCheck: (_, msg) => {
                if (typeof msg !== 'string') {
                    return false;
                }

                return msg === 'HeyFrame.State.get("cmsPageState") is deprecated! Use HeyFrame.Store.get instead.';
            },
        });

        HeyFrame.Store.register({
            id: 'cmsPage',
            state: () => ({
                foo: 'bar',
            }),
            actions: {
                setFoo(foo) {
                    this.foo = foo;
                },
            },
        });

        const store = HeyFrame.Store.get('cmsPage');
        expect(store.foo).toBe('bar');

        store.setFoo('jest');
        expect(store.foo).toBe('jest');

        HeyFrame.Store.unregister('cmsPage');
    });
});
