/**
 * @sw-package innovation
 *
 * @private
 * @description Apply for upselling service only, no public usage
 */

import 'src/app/store/teaser-popover.store';
import type { TeaserChannelConfig, TeaserPopoverConfig } from 'src/app/store/teaser-popover.store';
// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default function initializeTeaserPopovers(): void {
    const store = HeyFrame.Store.get('teaserPopover');

    HeyFrame.ExtensionAPI.handle(
        // @ts-expect-error - There are no types for this as it is private API
        '__upsellingTeaserPopover',
        (configuration: TeaserChannelConfig | TeaserPopoverConfig) => {
            if (configuration.positionId === 'channel') {
                // eslint-disable-next-line @typescript-eslint/no-unsafe-call,@typescript-eslint/no-unsafe-member-access
                store.addChannel(configuration as TeaserChannelConfig);
                return;
            }

            // eslint-disable-next-line @typescript-eslint/no-unsafe-call,@typescript-eslint/no-unsafe-member-access
            store.addPopoverComponent(configuration as TeaserPopoverConfig);
        },
    );
}
