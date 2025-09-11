/**
 * @sw-package framework
 */

import Punycode from 'punycode';

/**
 * @private
 */
HeyFrame.Filter.register('unicodeUri', (value: string) => {
    if (!value) {
        return '';
    }

    const unicode = Punycode.toUnicode(value);

    return decodeURI(unicode);
});

/* @private */
export {};
