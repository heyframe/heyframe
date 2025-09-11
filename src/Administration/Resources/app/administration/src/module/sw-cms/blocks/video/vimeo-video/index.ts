/**
 * @private
 * @sw-package discovery
 */
HeyFrame.Component.register('sw-cms-preview-vimeo-video', () => import('./preview'));
/**
 * @private
 * @sw-package discovery
 */
HeyFrame.Component.register('sw-cms-block-vimeo-video', () => import('./component'));

/**
 * @private
 * @sw-package discovery
 */
HeyFrame.Service('cmsService').registerCmsBlock({
    name: 'vimeo-video',
    label: 'sw-cms.blocks.video.vimeoVideo.label',
    category: 'video',
    component: 'sw-cms-block-vimeo-video',
    previewComponent: 'sw-cms-preview-vimeo-video',
    defaultConfig: {
        marginBottom: '20px',
        marginTop: '20px',
        marginLeft: null,
        marginRight: null,
        sizingMode: 'boxed',
    },
    slots: {
        video: 'vimeo-video',
    },
});
