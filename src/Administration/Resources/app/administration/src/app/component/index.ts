/**
 * @sw-package framework
 * @private
 */
export default () => {
    /* eslint-disable sw-deprecation-rules/private-feature-declarations, max-len */
    HeyFrame.Component.register('sw-wizard-page', () => import('src/app/component/wizard/sw-wizard-page/index'));
    HeyFrame.Component.register(
        'sw-wizard-dot-navigation',
        () => import('src/app/component/wizard/sw-wizard-dot-navigation/index'),
    );
    HeyFrame.Component.register('sw-wizard', () => import('src/app/component/wizard/sw-wizard/index'));
    HeyFrame.Component.register('sw-vnode-renderer', () => import('src/app/component/utils/sw-vnode-renderer/index'));
    HeyFrame.Component.register('sw-verify-user-modal', () => import('src/app/component/utils/sw-verify-user-modal/index'));
    HeyFrame.Component.register('sw-upload-listener', () => import('src/app/component/utils/sw-upload-listener/index'));
    HeyFrame.Component.register('sw-time-ago', () => import('src/app/component/utils/sw-time-ago/index'));
    HeyFrame.Component.register('sw-text-preview', () => import('src/app/component/utils/sw-text-preview/index'));
    HeyFrame.Component.register('sw-step-item', () => import('src/app/component/utils/sw-step-item/index'));
    HeyFrame.Component.register('sw-step-display', () => import('src/app/component/utils/sw-step-display/index'));
    HeyFrame.Component.register('sw-status', () => import('src/app/component/utils/sw-status/index'));
    HeyFrame.Component.register(
        'sw-skeleton-bar-deprecated',
        () => import('src/app/component/utils/sw-skeleton-bar-deprecated/index'),
    );
    HeyFrame.Component.register('sw-skeleton-bar', () => import('src/app/component/utils/sw-skeleton-bar/index'));
    HeyFrame.Component.register('sw-skeleton', () => import('src/app/component/utils/sw-skeleton/index'));
    HeyFrame.Component.register(
        'sw-shortcut-overview-item',
        () => import('src/app/component/utils/sw-shortcut-overview-item/index'),
    );
    HeyFrame.Component.register('sw-shortcut-overview', () => import('src/app/component/utils/sw-shortcut-overview/index'));
    HeyFrame.Component.register('sw-provide', () => import('src/app/component/utils/sw-provide/index'));
    HeyFrame.Component.register('sw-progress-bar', () => import('src/app/component/utils/sw-progress-bar/index'));
    HeyFrame.Component.register(
        'sw-popover-deprecated',
        () => import('src/app/component/utils/sw-popover-deprecated/index'),
    );
    HeyFrame.Component.register('sw-popover', () => import('src/app/component/utils/sw-popover/index'));
    HeyFrame.Component.register('sw-overlay', () => import('src/app/component/utils/sw-overlay/index'));
    HeyFrame.Component.register('sw-notifications', () => import('src/app/component/utils/sw-notifications/index'));
    HeyFrame.Component.register(
        'sw-notification-center-item',
        () => import('src/app/component/utils/sw-notification-center-item/index'),
    );
    HeyFrame.Component.register(
        'sw-notification-center',
        () => import('src/app/component/utils/sw-notification-center/index'),
    );
    HeyFrame.Component.register('sw-loader-deprecated', () => import('src/app/component/utils/sw-loader-deprecated/index'));
    HeyFrame.Component.register('sw-loader', () => import('src/app/component/utils/sw-loader/index'));
    HeyFrame.Component.register('sw-license-violation', () => import('src/app/component/utils/sw-license-violation/index'));
    HeyFrame.Component.register('sw-internal-link', () => import('src/app/component/utils/sw-internal-link/index'));
    HeyFrame.Component.register('sw-inherit-wrapper', () => import('src/app/component/utils/sw-inherit-wrapper/index'));
    HeyFrame.Component.register('sw-ignore-class', () => import('src/app/component/utils/sw-ignore-class/index'));
    HeyFrame.Component.register('sw-external-link', () => import('src/app/component/utils/sw-external-link/index'));
    HeyFrame.Component.register('sw-error-boundary', () => import('src/app/component/utils/sw-error-boundary/index'));
    HeyFrame.Component.register(
        'sw-duplicated-media-v2',
        () => import('src/app/component/utils/sw-duplicated-media-v2/index'),
    );
    HeyFrame.Component.register('sw-color-badge', () => import('src/app/component/utils/sw-color-badge/index'));
    HeyFrame.Component.register('sw-tree-item', () => import('src/app/component/tree/sw-tree-item/index'));
    HeyFrame.Component.register('sw-tree-input-field', () => import('src/app/component/tree/sw-tree-input-field/index'));
    HeyFrame.Component.register('sw-tree', () => import('src/app/component/tree/sw-tree/index'));
    HeyFrame.Component.register('sw-skip-link', () => import('src/app/component/structure/sw-skip-link/index'));
    HeyFrame.Component.register(
        'sw-sidebar-renderer',
        () => import('src/app/component/structure/sw-sidebar-renderer/index'),
    );
    HeyFrame.Component.register(
        'sw-search-more-results',
        () => import('src/app/component/structure/sw-search-more-results/index'),
    );
    HeyFrame.Component.register('sw-search-bar-item', () => import('src/app/component/structure/sw-search-bar-item/index'));
    HeyFrame.Component.register('sw-search-bar', () => import('src/app/component/structure/sw-search-bar/index'));
    HeyFrame.Component.register(
        'sw-sales-channel-switch',
        () => import('src/app/component/structure/sw-sales-channel-switch/index'),
    );
    HeyFrame.Component.register(
        'sw-sales-channel-config',
        () => import('src/app/component/structure/sw-sales-channel-config/index'),
    );
    HeyFrame.Component.register('sw-page', () => import('src/app/component/structure/sw-page/index'));
    HeyFrame.Component.register('sw-modals-renderer', () => import('src/app/component/structure/sw-modals-renderer/index'));
    HeyFrame.Component.register('sw-language-switch', () => import('src/app/component/structure/sw-language-switch/index'));
    HeyFrame.Component.register('sw-language-info', () => import('src/app/component/structure/sw-language-info/index'));
    HeyFrame.Component.register(
        'sw-inheritance-warning',
        () => import('src/app/component/structure/sw-inheritance-warning/index'),
    );
    HeyFrame.Component.register(
        'sw-in-app-purchase-checkout',
        () => import('src/app/component/structure/sw-in-app-purchase-checkout/index'),
    );
    HeyFrame.Component.register('sw-hidden-iframes', () => import('src/app/component/structure/sw-hidden-iframes/index'));
    HeyFrame.Component.register('sw-error', () => import('src/app/component/structure/sw-error/index'));
    HeyFrame.Component.register(
        'sw-discard-changes-modal',
        () => import('src/app/component/structure/sw-discard-changes-modal/index'),
    );
    HeyFrame.Component.register('sw-desktop', () => import('src/app/component/structure/sw-desktop/index'));
    HeyFrame.Component.register('sw-card-view', () => import('src/app/component/structure/sw-card-view/index'));
    HeyFrame.Component.register(
        'sw-block-parent',
        () => import('src/app/component/structure/sw-block-override/sw-block-parent/index'),
    );
    HeyFrame.Component.register('sw-block', () => import('src/app/component/structure/sw-block-override/sw-block/index'));
    HeyFrame.Component.register('sw-admin-menu-item', () => import('src/app/component/structure/sw-admin-menu-item/index'));
    HeyFrame.Component.register('sw-admin-menu', () => import('src/app/component/structure/sw-admin-menu/index'));
    HeyFrame.Component.register('sw-admin', () => import('src/app/component/structure/sw-admin/index'));
    HeyFrame.Component.register(
        'sw-sidebar-navigation-item',
        () => import('src/app/component/sidebar/sw-sidebar-navigation-item/index'),
    );
    HeyFrame.Component.register('sw-sidebar-item', () => import('src/app/component/sidebar/sw-sidebar-item/index'));
    HeyFrame.Component.register('sw-sidebar', () => import('src/app/component/sidebar/sw-sidebar/index'));
    HeyFrame.Component.register('sw-rule-modal', () => import('src/app/component/rule/sw-rule-modal/index'));
    HeyFrame.Component.register(
        'sw-condition-unit-menu',
        () => import('src/app/component/rule/sw-condition-unit-menu/index'),
    );
    HeyFrame.Component.register(
        'sw-condition-type-select',
        () => import('src/app/component/rule/sw-condition-type-select/index'),
    );
    HeyFrame.Component.register(
        'sw-condition-tree-node',
        () => import('src/app/component/rule/sw-condition-tree-node/index'),
    );
    HeyFrame.Component.register('sw-condition-tree', () => import('src/app/component/rule/sw-condition-tree/index'));
    HeyFrame.Component.register(
        'sw-condition-or-container',
        () => import('src/app/component/rule/sw-condition-or-container/index'),
    );
    HeyFrame.Component.register(
        'sw-condition-operator-select',
        () => import('src/app/component/rule/sw-condition-operator-select/index'),
    );
    HeyFrame.Component.register('sw-condition-modal', () => import('src/app/component/rule/sw-condition-modal/index'));
    HeyFrame.Component.register('sw-condition-base', () => import('src/app/component/rule/sw-condition-base/index'));
    HeyFrame.Component.register(
        'sw-condition-and-container',
        () => import('src/app/component/rule/sw-condition-and-container/index'),
    );
    HeyFrame.Component.register(
        'sw-condition-all-line-items-container',
        () => import('src/app/component/rule/sw-condition-all-line-items-container/index'),
    );
    HeyFrame.Component.register('sw-arrow-field', () => import('src/app/component/rule/sw-arrow-field/index'));
    HeyFrame.Component.register(
        'sw-search-preferences-modal',
        () => import('src/app/component/modal/sw-search-preferences-modal/index'),
    );
    HeyFrame.Component.register(
        'sw-image-preview-modal',
        () => import('src/app/component/modal/sw-image-preview-modal/index'),
    );
    HeyFrame.Component.register('sw-confirm-modal', () => import('src/app/component/modal/sw-confirm-modal/index'));
    HeyFrame.Component.register('mt-text-editor', () => import('src/app/component/meteor-wrapper/mt-text-editor/index'));
    HeyFrame.Component.register(
        'sw-text-editor-toolbar-button-link',
        () => import('src/app/component/meteor-wrapper/mt-text-editor/sw-text-editor-toolbar-button-link/index'),
    );
    HeyFrame.Component.register('mt-tabs', () => import('src/app/component/meteor-wrapper/mt-tabs/index'));
    HeyFrame.Component.register('mt-datepicker', () => import('src/app/component/meteor-wrapper/mt-datepicker/index'));
    HeyFrame.Component.register('mt-card', () => import('src/app/component/meteor-wrapper/mt-card/index'));
    HeyFrame.Component.register(
        'sw-meteor-single-select',
        () => import('src/app/component/meteor/sw-meteor-single-select/index'),
    );
    HeyFrame.Component.register('sw-meteor-page', () => import('src/app/component/meteor/sw-meteor-page/index'));
    HeyFrame.Component.register('sw-meteor-navigation', () => import('src/app/component/meteor/sw-meteor-navigation/index'));
    HeyFrame.Component.register('sw-meteor-card', () => import('src/app/component/meteor/sw-meteor-card/index'));
    HeyFrame.Component.register('sw-sortable-list', () => import('src/app/component/list/sw-sortable-list/index'));
    HeyFrame.Component.register('sw-pagination', () => import('src/app/component/grid/sw-pagination/index'));
    HeyFrame.Component.register('sw-grid-row', () => import('src/app/component/grid/sw-grid-row/index'));
    HeyFrame.Component.register('sw-grid-column', () => import('src/app/component/grid/sw-grid-column/index'));
    HeyFrame.Component.register('sw-grid', () => import('src/app/component/grid/sw-grid/index'));
    HeyFrame.Component.register('sw-url-field', () => import('src/app/component/form/sw-url-field/index'));
    HeyFrame.Component.register(
        'sw-textarea-field-deprecated',
        () => import('src/app/component/form/sw-textarea-field-deprecated/index'),
    );
    HeyFrame.Component.register('sw-textarea-field', () => import('src/app/component/form/sw-textarea-field/index'));
    HeyFrame.Component.register(
        'sw-text-field-deprecated',
        () => import('src/app/component/form/sw-text-field-deprecated/index'),
    );
    HeyFrame.Component.register('sw-text-field', () => import('src/app/component/form/sw-text-field/index'));
    HeyFrame.Component.register('sw-text-editor', () => import('src/app/component/form/sw-text-editor/index'));
    HeyFrame.Component.register(
        'sw-text-editor-toolbar-table-button',
        () => import('src/app/component/form/sw-text-editor/sw-text-editor-toolbar-table-button/index'),
    );
    HeyFrame.Component.register(
        'sw-text-editor-toolbar-button',
        () => import('src/app/component/form/sw-text-editor/sw-text-editor-toolbar-button/index'),
    );
    HeyFrame.Component.register(
        'sw-text-editor-toolbar',
        () => import('src/app/component/form/sw-text-editor/sw-text-editor-toolbar/index'),
    );
    HeyFrame.Component.register(
        'sw-text-editor-table-toolbar',
        () => import('src/app/component/form/sw-text-editor/sw-text-editor-table-toolbar/index'),
    );
    HeyFrame.Component.register(
        'sw-text-editor-link-menu',
        () => import('src/app/component/form/sw-text-editor/sw-text-editor-link-menu/index'),
    );
    HeyFrame.Component.register('sw-tagged-field', () => import('src/app/component/form/sw-tagged-field/index'));
    HeyFrame.Component.register('sw-switch-field', () => import('src/app/component/form/sw-switch-field/index'));
    HeyFrame.Component.register(
        'sw-snippet-field-edit-modal',
        () => import('src/app/component/form/sw-snippet-field-edit-modal/index'),
    );
    HeyFrame.Component.register('sw-snippet-field', () => import('src/app/component/form/sw-snippet-field/index'));
    HeyFrame.Component.register('sw-select-rule-create', () => import('src/app/component/form/sw-select-rule-create/index'));
    HeyFrame.Component.register('sw-select-option', () => import('src/app/component/form/sw-select-option/index'));
    HeyFrame.Component.register(
        'sw-select-field-deprecated',
        () => import('src/app/component/form/sw-select-field-deprecated/index'),
    );
    HeyFrame.Component.register('sw-select-field', () => import('src/app/component/form/sw-select-field/index'));
    HeyFrame.Component.register('sw-radio-field', () => import('src/app/component/form/sw-radio-field/index'));
    HeyFrame.Component.register(
        'sw-purchase-price-field',
        () => import('src/app/component/form/sw-purchase-price-field/index'),
    );
    HeyFrame.Component.register('sw-price-field', () => import('src/app/component/form/sw-price-field/index'));
    HeyFrame.Component.register('sw-password-field', () => import('src/app/component/form/sw-password-field/index'));
    HeyFrame.Component.register('sw-number-field', () => import('src/app/component/form/sw-number-field/index'));
    HeyFrame.Component.register(
        'sw-maintain-currencies-modal',
        () => import('src/app/component/form/sw-maintain-currencies-modal/index'),
    );
    HeyFrame.Component.register('sw-list-price-field', () => import('src/app/component/form/sw-list-price-field/index'));
    HeyFrame.Component.register('sw-gtc-checkbox', () => import('src/app/component/form/sw-gtc-checkbox/index'));
    HeyFrame.Component.register(
        'sw-form-field-renderer',
        () => import('src/app/component/form/sw-form-field-renderer/index'),
    );
    HeyFrame.Component.register('sw-file-input', () => import('src/app/component/form/sw-file-input/index'));
    HeyFrame.Component.register('sw-field-copyable', () => import('src/app/component/form/sw-field-copyable/index'));
    HeyFrame.Component.register('sw-email-field', () => import('src/app/component/form/sw-email-field/index'));
    HeyFrame.Component.register('sw-dynamic-url-field', () => import('src/app/component/form/sw-dynamic-url-field/index'));
    HeyFrame.Component.register(
        'sw-custom-field-set-renderer',
        () => import('src/app/component/form/sw-custom-field-set-renderer/index'),
    );
    HeyFrame.Component.register('sw-confirm-field', () => import('src/app/component/form/sw-confirm-field/index'));
    HeyFrame.Component.register(
        'sw-colorpicker-deprecated',
        () => import('src/app/component/form/sw-colorpicker-deprecated/index'),
    );
    HeyFrame.Component.register('sw-colorpicker', () => import('src/app/component/form/sw-colorpicker/index'));
    HeyFrame.Component.register(
        'sw-checkbox-field-deprecated',
        () => import('src/app/component/form/sw-checkbox-field-deprecated/index'),
    );
    HeyFrame.Component.register('sw-checkbox-field', () => import('src/app/component/form/sw-checkbox-field/index'));
    HeyFrame.Component.register(
        'sw-boolean-radio-group',
        () => import('src/app/component/form/sw-boolean-radio-group/index'),
    );
    HeyFrame.Component.register(
        'sw-entity-single-select',
        () => import('src/app/component/form/select/entity/sw-entity-single-select/index'),
    );
    HeyFrame.Component.register(
        'sw-entity-multi-select',
        () => import('src/app/component/form/select/entity/sw-entity-multi-select/index'),
    );
    HeyFrame.Component.register(
        'sw-entity-multi-id-select',
        () => import('src/app/component/form/select/entity/sw-entity-multi-id-select/index'),
    );
    HeyFrame.Component.register(
        'sw-entity-many-to-many-select',
        () => import('src/app/component/form/select/entity/sw-entity-many-to-many-select/index'),
    );
    HeyFrame.Component.register(
        'sw-entity-advanced-selection-modal',
        () => import('src/app/component/form/select/entity/sw-entity-advanced-selection-modal/index'),
    );
    HeyFrame.Component.register(
        'sw-advanced-selection-rule',
        () => import('src/app/component/form/select/entity/advanced-selection-entities/sw-advanced-selection-rule/index'),
    );
    HeyFrame.Component.register(
        'sw-advanced-selection-product',
        () => import('src/app/component/form/select/entity/advanced-selection-entities/sw-advanced-selection-product/index'),
    );
    HeyFrame.Component.register(
        'sw-single-select',
        () => import('src/app/component/form/select/base/sw-single-select/index'),
    );
    HeyFrame.Component.register(
        'sw-select-selection-list',
        () => import('src/app/component/form/select/base/sw-select-selection-list/index'),
    );
    HeyFrame.Component.register(
        'sw-select-result-list',
        () => import('src/app/component/form/select/base/sw-select-result-list/index'),
    );
    HeyFrame.Component.register(
        'sw-select-result',
        () => import('src/app/component/form/select/base/sw-select-result/index'),
    );
    HeyFrame.Component.register('sw-select-base', () => import('src/app/component/form/select/base/sw-select-base/index'));
    HeyFrame.Component.register(
        'sw-multi-tag-select',
        () => import('src/app/component/form/select/base/sw-multi-tag-select/index'),
    );
    HeyFrame.Component.register('sw-multi-select', () => import('src/app/component/form/select/base/sw-multi-select/index'));
    HeyFrame.Component.register('sw-field-error', () => import('src/app/component/form/field-base/sw-field-error/index'));
    HeyFrame.Component.register(
        'sw-contextual-field',
        () => import('src/app/component/form/field-base/sw-contextual-field/index'),
    );
    HeyFrame.Component.register('sw-block-field', () => import('src/app/component/form/field-base/sw-block-field/index'));
    HeyFrame.Component.register('sw-base-field', () => import('src/app/component/form/field-base/sw-base-field/index'));
    HeyFrame.Component.register(
        'sw-sidebar-filter-panel',
        () => import('src/app/component/filter/sw-sidebar-filter-panel/index'),
    );
    HeyFrame.Component.register('sw-range-filter', () => import('src/app/component/filter/sw-range-filter/index'));
    HeyFrame.Component.register('sw-number-filter', () => import('src/app/component/filter/sw-number-filter/index'));
    HeyFrame.Component.register(
        'sw-multi-select-filter',
        () => import('src/app/component/filter/sw-multi-select-filter/index'),
    );
    HeyFrame.Component.register('sw-filter-panel', () => import('src/app/component/filter/sw-filter-panel/index'));
    HeyFrame.Component.register('sw-existence-filter', () => import('src/app/component/filter/sw-existence-filter/index'));
    HeyFrame.Component.register('sw-date-filter', () => import('src/app/component/filter/sw-date-filter/index'));
    HeyFrame.Component.register('sw-boolean-filter', () => import('src/app/component/filter/sw-boolean-filter/index'));
    HeyFrame.Component.register('sw-base-filter', () => import('src/app/component/filter/sw-base-filter/index'));
    HeyFrame.Component.register(
        'sw-iframe-renderer',
        () => import('src/app/component/extension-api/sw-iframe-renderer/index'),
    );
    HeyFrame.Component.register(
        'sw-extension-teaser-sales-channel',
        () => import('src/app/component/extension-api/sw-extension-teaser-sales-channel/index'),
    );
    HeyFrame.Component.register(
        'sw-extension-teaser-popover',
        () => import('src/app/component/extension-api/sw-extension-teaser-popover/index'),
    );
    HeyFrame.Component.register(
        'sw-extension-component-section',
        () => import('src/app/component/extension-api/sw-extension-component-section/index'),
    );
    HeyFrame.Component.register(
        'sw-product-stream-grid-preview',
        () => import('src/app/component/entity/sw-product-stream-grid-preview/index'),
    );
    HeyFrame.Component.register(
        'sw-many-to-many-assignment-card',
        () => import('src/app/component/entity/sw-many-to-many-assignment-card/index'),
    );
    HeyFrame.Component.register(
        'sw-category-tree-field',
        () => import('src/app/component/entity/sw-category-tree-field/index'),
    );
    HeyFrame.Component.register('sw-bulk-edit-modal', () => import('src/app/component/entity/sw-bulk-edit-modal/index'));
    HeyFrame.Component.register(
        'sw-data-grid-skeleton',
        () => import('src/app/component/data-grid/sw-data-grid-skeleton/index'),
    );
    HeyFrame.Component.register(
        'sw-data-grid-settings',
        () => import('src/app/component/data-grid/sw-data-grid-settings/index'),
    );
    HeyFrame.Component.register(
        'sw-data-grid-inline-edit',
        () => import('src/app/component/data-grid/sw-data-grid-inline-edit/index'),
    );
    HeyFrame.Component.register(
        'sw-data-grid-column-position',
        () => import('src/app/component/data-grid/sw-data-grid-column-position/index'),
    );
    HeyFrame.Component.register(
        'sw-data-grid-column-boolean',
        () => import('src/app/component/data-grid/sw-data-grid-column-boolean/index'),
    );
    HeyFrame.Component.register('sw-data-grid', () => import('src/app/component/data-grid/sw-data-grid/index'));
    HeyFrame.Component.register(
        'sw-context-menu-item',
        () => import('src/app/component/context-menu/sw-context-menu-item/index'),
    );
    HeyFrame.Component.register(
        'sw-context-menu-divider',
        () => import('src/app/component/context-menu/sw-context-menu-divider/index'),
    );
    HeyFrame.Component.register('sw-context-menu', () => import('src/app/component/context-menu/sw-context-menu/index'));
    HeyFrame.Component.register('sw-context-button', () => import('src/app/component/context-menu/sw-context-button/index'));
    HeyFrame.Component.register('sw-version', () => import('src/app/component/base/sw-version/index'));
    HeyFrame.Component.register('sw-user-card', () => import('src/app/component/base/sw-user-card/index'));
    HeyFrame.Component.register('sw-tabs-item', () => import('src/app/component/base/sw-tabs-item/index'));
    HeyFrame.Component.register('sw-tabs-deprecated', () => import('src/app/component/base/sw-tabs-deprecated/index'));
    HeyFrame.Component.register('sw-tabs', () => import('src/app/component/base/sw-tabs/index'));
    HeyFrame.Component.register('sw-sorting-select', () => import('src/app/component/base/sw-sorting-select/index'));
    HeyFrame.Component.register(
        'sw-simple-search-field',
        () => import('src/app/component/base/sw-simple-search-field/index'),
    );
    HeyFrame.Component.register('sw-rating-stars', () => import('src/app/component/base/sw-rating-stars/index'));
    HeyFrame.Component.register('sw-radio-panel', () => import('src/app/component/base/sw-radio-panel/index'));
    HeyFrame.Component.register('sw-property-search', () => import('src/app/component/base/sw-property-search/index'));
    HeyFrame.Component.register(
        'sw-product-variant-info',
        () => import('src/app/component/base/sw-product-variant-info/index'),
    );
    HeyFrame.Component.register('sw-product-image', () => import('src/app/component/base/sw-product-image/index'));
    HeyFrame.Component.register('sw-modal', () => import('src/app/component/base/sw-modal/index'));
    HeyFrame.Component.register('sw-label', () => import('src/app/component/base/sw-label/index'));
    HeyFrame.Component.register('sw-inheritance-switch', () => import('src/app/component/base/sw-inheritance-switch/index'));
    HeyFrame.Component.register('sw-icon-deprecated', () => import('src/app/component/base/sw-icon-deprecated/index'));
    HeyFrame.Component.register('sw-icon', () => import('src/app/component/base/sw-icon/index'));
    HeyFrame.Component.register('sw-highlight-text', () => import('src/app/component/base/sw-highlight-text/index'));
    HeyFrame.Component.register('sw-help-text', () => import('src/app/component/base/sw-help-text/index'));
    HeyFrame.Component.register('sw-error-summary', () => import('src/app/component/base/sw-error-summary/index'));
    HeyFrame.Component.register('sw-empty-state', () => import('src/app/component/base/sw-empty-state/index'));
    HeyFrame.Component.register('sw-description-list', () => import('src/app/component/base/sw-description-list/index'));
    HeyFrame.Component.register('sw-container', () => import('src/app/component/base/sw-container/index'));
    HeyFrame.Component.register('sw-collapse', () => import('src/app/component/base/sw-collapse/index'));
    HeyFrame.Component.register('sw-circle-icon', () => import('src/app/component/base/sw-circle-icon/index'));
    HeyFrame.Component.register('sw-chart-card', () => import('src/app/component/base/sw-chart-card/index'));
    HeyFrame.Component.register('sw-card-section', () => import('src/app/component/base/sw-card-section/index'));
    HeyFrame.Component.register('sw-card-filter', () => import('src/app/component/base/sw-card-filter/index'));
    HeyFrame.Component.register('sw-card-deprecated', () => import('src/app/component/base/sw-card-deprecated/index'));
    HeyFrame.Component.register('sw-card', () => import('src/app/component/base/sw-card/index'));
    HeyFrame.Component.register('sw-button-process', () => import('src/app/component/base/sw-button-process/index'));
    HeyFrame.Component.register('sw-button-group', () => import('src/app/component/base/sw-button-group/index'));
    HeyFrame.Component.register('sw-button-deprecated', () => import('src/app/component/base/sw-button-deprecated/index'));
    HeyFrame.Component.register('sw-button', () => import('src/app/component/base/sw-button/index'));
    HeyFrame.Component.register('sw-avatar', () => import('src/app/component/base/sw-avatar/index'));
    HeyFrame.Component.register('sw-alert-deprecated', () => import('src/app/component/base/sw-alert-deprecated/index'));
    HeyFrame.Component.register('sw-alert', () => import('src/app/component/base/sw-alert/index'));
    HeyFrame.Component.register('sw-address', () => import('src/app/component/base/sw-address/index'));
    HeyFrame.Component.register(
        'sw-app-wrong-app-url-modal',
        () => import('src/app/component/app/sw-app-wrong-app-url-modal/index'),
    );
    HeyFrame.Component.register('sw-app-topbar-button', () => import('src/app/component/app/sw-app-topbar-button/index'));
    HeyFrame.Component.register('sw-app-topbar-sidebar', () => import('src/app/component/app/sw-app-topbar-sidebar/index'));
    HeyFrame.Component.register(
        'sw-app-shop-id-change-modal',
        () => import('src/app/component/app/sw-app-shop-id-change-modal/index'),
    );
    HeyFrame.Component.register('sw-app-actions', () => import('src/app/component/app/sw-app-actions/index'));
    HeyFrame.Component.register('sw-app-action-button', () => import('src/app/component/app/sw-app-action-button/index'));
    HeyFrame.Component.register('sw-code-editor', () => import('src/app/component/form/sw-code-editor'));
    HeyFrame.Component.register('sw-datepicker', () => import('src/app/component/form/sw-datepicker'));
    HeyFrame.Component.register('sw-datepicker-deprecated', () => import('src/app/component/form/sw-datepicker-deprecated'));
    HeyFrame.Component.register('sw-chart', () => import('src/app/component/base/sw-chart'));
    HeyFrame.Component.register('sw-help-center-v2', () => import('src/app/component/utils/sw-help-center'));
    HeyFrame.Component.register('sw-help-sidebar', () => import('src/app/component/sidebar/sw-help-sidebar'));
    HeyFrame.Component.register('sw-image-slider', () => import('src/app/component/media/sw-image-slider'));
    HeyFrame.Component.register(
        'sw-media-add-thumbnail-form',
        () => import('src/app/component/media/sw-media-add-thumbnail-form'),
    );
    HeyFrame.Component.register('sw-media-base-item', () => import('src/app/component/media/sw-media-base-item'));
    HeyFrame.Component.extend(
        'sw-media-compact-upload-v2',
        'sw-media-upload-v2',
        () => import('src/app/component/media/sw-media-compact-upload-v2'),
    );
    HeyFrame.Component.register('sw-media-entity-mapper', () => import('src/app/component/media/sw-media-entity-mapper'));
    HeyFrame.Component.register('sw-media-field', () => import('src/app/component/media/sw-media-field'));
    HeyFrame.Component.register('sw-media-folder-content', () => import('src/app/component/media/sw-media-folder-content'));
    HeyFrame.Component.register('sw-media-folder-item', () => import('src/app/component/media/sw-media-folder-item'));
    HeyFrame.Component.register(
        'sw-media-list-selection-item-v2',
        () => import('src/app/component/media/sw-media-list-selection-item-v2'),
    );
    HeyFrame.Component.register(
        'sw-media-list-selection-v2',
        () => import('src/app/component/media/sw-media-list-selection-v2'),
    );
    HeyFrame.Component.register('sw-media-media-item', () => import('src/app/component/media/sw-media-media-item'));
    HeyFrame.Component.register('sw-media-modal-delete', () => import('src/app/component/media/sw-media-modal-delete'));
    HeyFrame.Component.register(
        'sw-media-modal-folder-dissolve',
        () => import('src/app/component/media/sw-media-modal-folder-dissolve'),
    );
    HeyFrame.Component.register(
        'sw-media-modal-folder-settings',
        () => import('src/app/component/media/sw-media-modal-folder-settings'),
    );
    HeyFrame.Component.register('sw-media-modal-move', () => import('src/app/component/media/sw-media-modal-move'));
    HeyFrame.Component.register('sw-media-modal-replace', () => import('src/app/component/media/sw-media-modal-replace'));
    HeyFrame.Component.register('sw-media-preview-v2', () => import('src/app/component/media/sw-media-preview-v2'));
    HeyFrame.Component.extend('sw-media-replace', 'sw-media-upload-v2', import('src/app/component/media/sw-media-replace'));
    HeyFrame.Component.register('sw-media-upload-v2', () => import('src/app/component/media/sw-media-upload-v2'));
    HeyFrame.Component.register('sw-media-url-form', () => import('src/app/component/media/sw-media-url-form'));
    HeyFrame.Component.register('sw-sidebar-media-item', () => import('src/app/component/media/sw-sidebar-media-item'));
    HeyFrame.Component.register('sw-extension-icon', () => import('src/app/component/extension/sw-extension-icon'));
    HeyFrame.Component.register('sw-ai-copilot-badge', () => import('src/app/component/feedback/sw-ai-copilot-badge'));
    HeyFrame.Component.register('sw-ai-copilot-warning', () => import('src/app/component/feedback/sw-ai-copilot-warning'));
    HeyFrame.Component.register('sw-string-filter', () => import('src/app/component/filter/sw-string-filter'));
    HeyFrame.Component.register(
        'sw-media-modal-renderer',
        () => import('src/app/component/structure/sw-media-modal-renderer/index'),
    );
    HeyFrame.Component.extend('sw-sidebar-collapse', 'sw-collapse', () => import('./sidebar/sw-sidebar-collapse/index'));
    HeyFrame.Component.extend(
        'sw-condition-is-net-select',
        'sw-condition-operator-select',
        () => import('./rule/sw-condition-is-net-select/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-base-line-item',
        'sw-condition-base',
        () => import('./rule/sw-condition-base-line-item/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-time-range',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-time-range/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-shipping-zip-code',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-shipping-zip-code/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-script',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-script/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-order-custom-field',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-order-custom-field/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-not-found',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-not-found/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-line-item-with-quantity',
        'sw-condition-base-line-item',
        () => import('./rule/condition-type/sw-condition-line-item-with-quantity/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-line-item-purchase-price',
        'sw-condition-base-line-item',
        () => import('./rule/condition-type/sw-condition-line-item-purchase-price/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-line-item-property',
        'sw-condition-base-line-item',
        () => import('./rule/condition-type/sw-condition-line-item-property/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-line-item-in-category',
        'sw-condition-base-line-item',
        () => import('./rule/condition-type/sw-condition-line-item-in-category/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-line-item-goods-total',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-line-item-goods-total/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-line-item-custom-field',
        'sw-condition-base-line-item',
        () => import('./rule/condition-type/sw-condition-line-item-custom-field/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-line-item',
        'sw-condition-base-line-item',
        () => import('./rule/condition-type/sw-condition-line-item/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-is-always-valid',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-is-always-valid/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-goods-price',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-goods-price/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-goods-count',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-goods-count/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-generic-line-item',
        'sw-condition-base-line-item',
        () => import('./rule/condition-type/sw-condition-generic-line-item/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-generic',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-generic/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-date-range',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-date-range/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-customer-custom-field',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-customer-custom-field/index'),
    );
    HeyFrame.Component.extend(
        'sw-condition-billing-zip-code',
        'sw-condition-base',
        () => import('./rule/condition-type/sw-condition-billing-zip-code/index'),
    );
    HeyFrame.Component.extend(
        'sw-url-field-deprecated',
        'sw-text-field-deprecated',
        () => import('./form/sw-url-field-deprecated/index'),
    );
    HeyFrame.Component.extend(
        'sw-switch-field-deprecated',
        'sw-checkbox-field-deprecated',
        () => import('./form/sw-switch-field-deprecated/index'),
    );
    HeyFrame.Component.extend(
        'sw-select-number-field',
        'sw-select-field-deprecated',
        () => import('./form/sw-select-number-field/index'),
    );
    HeyFrame.Component.extend(
        'sw-password-field-deprecated',
        'sw-text-field-deprecated',
        () => import('./form/sw-password-field-deprecated/index'),
    );
    HeyFrame.Component.extend(
        'sw-number-field-deprecated',
        'sw-text-field-deprecated',
        () => import('./form/sw-number-field-deprecated/index'),
    );
    HeyFrame.Component.extend(
        'sw-email-field-deprecated',
        'sw-text-field-deprecated',
        () => import('./form/sw-email-field-deprecated/index'),
    );
    HeyFrame.Component.extend(
        'sw-compact-colorpicker',
        'sw-colorpicker-deprecated',
        () => import('./form/sw-compact-colorpicker/index'),
    );
    HeyFrame.Component.extend(
        'sw-entity-tag-select',
        'sw-entity-multi-select',
        () => import('./form/select/entity/sw-entity-tag-select/index'),
    );
    HeyFrame.Component.extend(
        'sw-entity-advanced-selection-modal-grid',
        'sw-entity-listing',
        () => import('./form/select/entity/sw-entity-advanced-selection-modal-grid/index'),
    );
    HeyFrame.Component.extend(
        'sw-multi-tag-ip-select',
        'sw-multi-tag-select',
        () => import('./form/select/base/sw-multi-tag-ip-select/index'),
    );
    HeyFrame.Component.extend(
        'sw-grouped-single-select',
        'sw-single-select',
        () => import('./form/select/base/sw-grouped-single-select/index'),
    );
    HeyFrame.Component.extend('sw-one-to-many-grid', 'sw-data-grid', () => import('./entity/sw-one-to-many-grid/index'));
    HeyFrame.Component.extend('sw-entity-listing', 'sw-data-grid', () => import('./entity/sw-entity-listing/index'));
    HeyFrame.Component.extend('sw-price-preview', 'sw-price-field', () => import('./base/sw-price-preview/index'));
    /* eslint-enable sw-deprecation-rules/private-feature-declarations, max-len */
};
