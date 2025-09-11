import { MtDatepicker as MtDatepickerOriginal } from '@heyframe-ag/meteor-component-library';
// eslint-disable-next-line max-len
import type { DateTimeOptions } from 'vue-i18n';
import template from './mt-datepicker.html.twig';

/**
 * @sw-package framework
 *
 * @private
 * @status ready
 * @description Wrapper component for mt-datepicker. Replaces the
 * datepicker with automatic language and formatting for the admin user.
 */
export default HeyFrame.Component.wrapComponentConfig({
    template,

    components: {
        'mt-datepicker-original': MtDatepickerOriginal,
    },

    props: {
        /**
         * Sets the locale for the date picker.
         * This affects things like the language used for month names and weekdays.
         */
        locale: {
            type: String as PropType<string>,
            required: false,
        },

        /**
         * The format of the date picker.
         * You can use a string or a function to format the date.
         */
        format: {
            type: Function,
            required: false,
            default: undefined,
        },

        /**
         * Defines the time zone for the date picker.
         * Useful for adjusting date and time according to a specific timezone.
         */
        timeZone: {
            type: String as PropType<string>,
            required: false,
        },

        /**
         * Defines the type of the date picker.
         * Options: "date" (for selecting a date), or "datetime" (for selecting both).
         */
        dateType: {
            type: String as PropType<'date' | 'datetime' | 'time'>,
            required: false,
            default: 'datetime',
        },

        /**
         * Determines if the timepicker is in 24 or 12 hour format
         */
        is24: {
            type: Boolean as PropType<boolean>,
            required: false,
        },
    },

    computed: {
        userLocale(): string {
            return HeyFrame.Store.get('session').currentLocale || 'en-US';
        },

        userTimeZone() {
            return HeyFrame?.Store?.get('session')?.currentUser?.timeZone ?? 'UTC';
        },

        is24HourFormat(): boolean {
            if (this.is24) {
                return this.is24 as boolean;
            }

            const locale = HeyFrame.Store.get('session').currentLocale!;
            const formatter = new Intl.DateTimeFormat(locale, { hour: 'numeric' });
            const intlOptions = formatter.resolvedOptions();
            return !intlOptions.hour12;
        },

        formatterOptions(): DateTimeOptions {
            const defaultFormat = {
                hour12: !this.is24HourFormat,
                locale: this.userLocale,
            };

            let format: {
                year?: 'numeric';
                month?: '2-digit' | 'numeric';
                day?: '2-digit' | 'numeric';
                hour?: '2-digit' | 'numeric';
                minute?: '2-digit' | 'numeric';
            } = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
            };

            if (this.dateType === 'time') {
                format = {
                    hour: '2-digit',
                    minute: '2-digit',
                };
            }

            if (this.dateType === 'date') {
                format = {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                };
            }

            return {
                ...defaultFormat,
                ...format,
            };
        },
    },

    methods: {
        customFormat(date: Date): string {
            const currentLocale = HeyFrame.Store.get('session').currentLocale || 'en-US';
            const formatter = new Intl.DateTimeFormat(currentLocale, this.formatterOptions);

            return formatter.format(new Date(date));
        },
    },
});
