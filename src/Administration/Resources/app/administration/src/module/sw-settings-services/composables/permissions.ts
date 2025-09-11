/**
 * @sw-package framework
 */
import { useHeyFrameServicesStore } from '../store/heyframe-services.store';

/* eslint-disable import/prefer-default-export */
/**
 * @private
 */
export async function grantPermissions() {
    const heyframeServiceStore = useHeyFrameServicesStore();
    const currentRevision = heyframeServiceStore.currentRevision?.revision;

    if (!currentRevision) {
        throw new Error('No revision available');
    }

    await HeyFrame.Service('heyframeServicesService').acceptRevision(currentRevision);

    window.location.reload();
}

/**
 * @private
 */
export async function revokePermissions() {
    await HeyFrame.Service('heyframeServicesService').revokePermissions();

    window.location.reload();
}
/* eslint-enable import/prefer-default-export */
