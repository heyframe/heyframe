import type { HeyFrameClass } from 'src/core/heyframe';
import useSession from '../../../app/composables/use-session';
import './extensions.store';

let initialLoad = false;

/**
 * @sw-package checkout
 * @private
 */
export default function initState(HeyFrame: HeyFrameClass): void {
    HeyFrame.Vue.watch(useSession().languageId, async () => {
        if (!HeyFrame.Service('acl').can('system.plugin_maintain')) {
            return;
        }

        // Always on page load setAdminLocale will be called once. Catch it to not load refresh extensions
        if (!initialLoad) {
            initialLoad = true;
            return;
        }

        await HeyFrame.Service('heyframeExtensionService').updateExtensionData(false);
    });
}
