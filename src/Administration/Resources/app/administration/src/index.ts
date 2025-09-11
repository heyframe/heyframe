/**
 * @sw-package framework
 */
import './app/assets/scss/all.scss';

// Import the HeyFrame instance
void import('src/core/heyframe').then(async ({ HeyFrameInstance }) => {
    // Set the global HeyFrame instance
    window.HeyFrame = HeyFrameInstance;

    if (window._swLoginOverrides) {
        window._swLoginOverrides.forEach((script) => {
            script();
        });
    }

    // Import the main file
    await import('src/app/main');

    // Start the main application and fingers crossed
    // that everything works as expected
    window.startApplication();
});
