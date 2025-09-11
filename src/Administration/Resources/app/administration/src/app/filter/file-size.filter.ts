/**
 * @sw-package framework
 */

/**
 * @private
 */
HeyFrame.Filter.register('fileSize', (value: number, locale: string) => {
    if (!value) {
        return '';
    }

    return HeyFrame.Utils.format.fileSize(value, locale);
});

/* @private */
export {};
