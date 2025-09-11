/**
 * @sw-package framework
 *
 * @private
 */
export default function initializeMediaModal(): void {
    // eslint-disable-next-line @typescript-eslint/require-await
    HeyFrame.ExtensionAPI.handle('uiMediaModalOpen', (modalConfig) => {
        HeyFrame.Store.get('mediaModal').openModal(modalConfig);
    });
}
