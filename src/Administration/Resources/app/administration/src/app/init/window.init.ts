/**
 * @sw-package framework
 */

/* Is covered by E2E tests */
/* istanbul ignore file */
import type { Router } from 'vue-router';

/**
 * @private
 */
export default function initializeWindow(): void {
    // Handle incoming window requests from the ExtensionAPI
    HeyFrame.ExtensionAPI.handle('windowReload', () => {
        window.location.reload();
    });

    HeyFrame.ExtensionAPI.handle('windowRedirect', ({ newTab, url }) => {
        if (newTab) {
            window.open(url, '_blank');
        } else {
            window.location.href = url;
        }
    });

    HeyFrame.ExtensionAPI.handle('windowRouterPush', async ({ name, params, path, replace }) => {
        const $router = HeyFrame.Application.view?.router as unknown as Router;

        if (!$router) {
            return;
        }

        await $router.push({
            // @ts-expect-error
            name: name && name.length > 0 ? name : undefined,
            params,
            path: path && path.length > 0 ? path : '',
            replace: replace ?? false,
        });
    });

    HeyFrame.ExtensionAPI.handle('windowRouterGetPath', () => {
        const $router = HeyFrame.Application.view?.router as unknown as Router;

        if (!$router) {
            return '';
        }

        return $router.currentRoute?.value?.fullPath || '';
    });
}
