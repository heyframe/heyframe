describe('sw-profile.store', () => {
    it('has initial state', () => {
        const store = HeyFrame.Store.get('swProfile');
        expect(store.minSearchTermLength).toBe(2);
        expect(store.searchPreferences).toStrictEqual([]);
        expect(store.userSearchPreferences).toBeNull();
    });

    it('has setMinSearchTermLength action', () => {
        const store = HeyFrame.Store.get('swProfile');
        expect(store.minSearchTermLength).toBe(2);
        store.setMinSearchTermLength(3);
        expect(store.minSearchTermLength).toBe(3);
    });
});
