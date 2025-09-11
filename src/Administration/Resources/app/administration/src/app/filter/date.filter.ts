/**
 * @sw-package framework
 */

HeyFrame.Filter.register('date', (value: string, options: Intl.DateTimeFormatOptions = {}): string => {
    if (!value) {
        return '';
    }

    return HeyFrame.Utils.format.date(value, options);
});

/**
 * @private
 */
export default {};
