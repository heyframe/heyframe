/**
 * @private
 * @sw-package discovery
 */
HeyFrame.Component.register('sw-cms-el-preview-sidebar-filter', () => import('./preview'));
/**
 * @private
 * @sw-package discovery
 */
HeyFrame.Component.register('sw-cms-el-config-sidebar-filter', () => import('./config'));
/**
 * @private
 * @sw-package discovery
 */
HeyFrame.Component.register('sw-cms-el-sidebar-filter', () => import('./component'));

/**
 * @private
 * @sw-package discovery
 */
HeyFrame.Service('cmsService').registerCmsElement({
    name: 'sidebar-filter',
    label: 'sw-cms.elements.sidebarFilter.label',
    component: 'sw-cms-el-sidebar-filter',
    configComponent: 'sw-cms-el-config-sidebar-filter',
    previewComponent: 'sw-cms-el-preview-sidebar-filter',
    allowedPageTypes: [HeyFrame.Constants.CMS.PAGE_TYPES.LISTING],
    disabledConfigInfoTextKey: 'sw-cms.elements.sidebarFilter.infoText.filterElement',
});
