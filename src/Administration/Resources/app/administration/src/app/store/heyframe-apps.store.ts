/**
 * @sw-package framework
 */
import type { AppModuleDefinition } from 'src/core/service/api/app-modules.service';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export interface HeyFrameAppsState {
    apps: AppModuleDefinition[];
    selectedIds: string[];
}

const heyframeApps = HeyFrame.Store.register({
    id: 'heyframeApps',

    state: (): {
        apps: AppModuleDefinition[];
        selectedIds: string[];
    } => ({
        apps: [],
        selectedIds: [],
    }),
});

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export type HeyFrameApps = ReturnType<typeof heyframeApps>;

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
export default heyframeApps;
