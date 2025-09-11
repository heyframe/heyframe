/**
 * @sw-package framework
 */
import type { CurrencyOptions } from 'src/core/service/utils/format.utils';

const { currency } = HeyFrame.Utils.format;

/**
 * @private
 */
HeyFrame.Filter.register(
    'currency',
    (value: string | boolean, format: string, decimalPlaces: number, additionalOptions: CurrencyOptions) => {
        if (
            (!value || value === true) &&
            (!HeyFrame.Utils.types.isNumber(value) || HeyFrame.Utils.types.isEqual(value, NaN))
        ) {
            return '-';
        }

        if (HeyFrame.Utils.types.isEqual(parseInt(value, 10), NaN)) {
            return value;
        }

        return currency(parseFloat(value), format, decimalPlaces, additionalOptions);
    },
);
