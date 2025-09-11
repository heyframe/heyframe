/**
 * @sw-package framework
 */
describe('heyframe-apps.store', () => {
    const store = HeyFrame.Store.get('heyframeApps');

    beforeEach(() => {
        store.$reset();
    });

    it('has initial state', () => {
        expect(store.apps).toStrictEqual([]);
        expect(store.selectedIds).toStrictEqual([]);
    });
});
