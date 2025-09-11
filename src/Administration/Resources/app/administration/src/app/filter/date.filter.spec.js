/**
 * @sw-package framework
 */
describe('src/app/filter/date.filter.ts', () => {
    const dateFilter = HeyFrame.Filter.getByName('date');

    HeyFrame.Utils.format.date = jest.fn();

    beforeEach(() => {
        HeyFrame.Utils.format.date.mockClear();
    });

    it('should contain a filter', () => {
        expect(dateFilter).toBeDefined();
    });

    it('should return empty string when no value is given', () => {
        expect(dateFilter()).toBe('');
    });

    it('should call the date format util for formatting', () => {
        dateFilter('01.01.1997', {
            myDateOptions: 'foo',
        });

        expect(HeyFrame.Utils.format.date).toHaveBeenCalledWith('01.01.1997', {
            myDateOptions: 'foo',
        });
    });
});
