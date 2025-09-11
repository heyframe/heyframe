/**
 * @sw-package framework
 */
import { computed, provide } from 'vue';

/**
 * @private
 */
export default HeyFrame.Component.wrapComponentConfig({
    template: '<slot />',
    inheritAttrs: false,
    setup(_props, { attrs }) {
        Object.keys(attrs).forEach((key) =>
            provide(
                HeyFrame.Utils.string.camelCase(key),
                computed(() => attrs[key]),
            ),
        );
        return {};
    },
});
