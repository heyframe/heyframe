/**
 * @sw-package fundamentals@after-sales
 */
import ImportExportService from './service/importExport.service';
import ImportExportProfileMappingService from './service/importExportProfileMapping.service';
import ImportExportProfileUpdateByService from './service/importExportUpdateByMapping.service';
import './acl';

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Service().register('importExport', () => {
    return new ImportExportService(HeyFrame.Application.getContainer('init').httpClient, HeyFrame.Service('loginService'));
});

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Service().register('importExportProfileMapping', () => {
    return new ImportExportProfileMappingService(HeyFrame.EntityDefinition);
});

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Service().register('importExportUpdateByMapping', () => {
    return new ImportExportProfileUpdateByService(HeyFrame.EntityDefinition);
});

/* eslint-disable max-len, sw-deprecation-rules/private-feature-declarations */
HeyFrame.Component.register('sw-import-export', () => import('./page/sw-import-export'));
HeyFrame.Component.register('sw-import-export-exporter', () => import('./component/sw-import-export-exporter'));
HeyFrame.Component.register('sw-import-export-importer', () => import('./component/sw-import-export-importer'));
HeyFrame.Component.register('sw-import-export-activity', () => import('./component/sw-import-export-activity'));
HeyFrame.Component.register(
    'sw-import-export-activity-log-info-modal',
    () => import('./component/sw-import-export-activity-log-info-modal'),
);
HeyFrame.Component.register(
    'sw-import-export-activity-result-modal',
    () => import('./component/sw-import-export-activity-result-modal'),
);
HeyFrame.Component.register(
    'sw-import-export-edit-profile-modal',
    () => import('./component/sw-import-export-edit-profile-modal'),
);
HeyFrame.Component.register(
    'sw-import-export-edit-profile-modal-mapping',
    () => import('./component/sw-import-export-edit-profile-modal-mapping'),
);
HeyFrame.Component.register(
    'sw-import-export-edit-profile-modal-identifiers',
    () => import('./component/sw-import-export-edit-profile-modal-identifiers'),
);
HeyFrame.Component.register(
    'sw-import-export-entity-path-select',
    () => import('./component/sw-import-export-entity-path-select'),
);
HeyFrame.Component.register(
    'sw-import-export-edit-profile-field-indicators',
    () => import('./component/sw-import-export-edit-profile-field-indicators'),
);
HeyFrame.Component.register(
    'sw-import-export-edit-profile-import-settings',
    () => import('./component/sw-import-export-edit-profile-import-settings'),
);
HeyFrame.Component.register(
    'sw-import-export-edit-profile-general',
    () => import('./component/sw-import-export-edit-profile-general'),
);
HeyFrame.Component.register(
    'sw-import-export-new-profile-wizard',
    () => import('./component/profile-wizard/sw-import-export-new-profile-wizard'),
);
HeyFrame.Component.register(
    'sw-import-export-new-profile-wizard-general-page',
    () => import('./component/profile-wizard/sw-import-export-new-profile-wizard-general-page'),
);
HeyFrame.Component.register(
    'sw-import-export-new-profile-wizard-csv-page',
    () => import('./component/profile-wizard/sw-import-export-new-profile-wizard-csv-page'),
);
HeyFrame.Component.register(
    'sw-import-export-new-profile-wizard-mapping-page',
    () => import('./component/profile-wizard/sw-import-export-new-profile-wizard-mapping-page'),
);
HeyFrame.Component.register('sw-import-export-view-import', () => import('./view/sw-import-export-view-import'));
HeyFrame.Component.register('sw-import-export-view-export', () => import('./view/sw-import-export-view-export'));
HeyFrame.Component.register('sw-import-export-view-profiles', () => import('./view/sw-import-export-view-profiles'));
HeyFrame.Component.register('sw-import-export-progress', () => import('./component/sw-import-export-progress'));
/* eslint-enable max-len, sw-deprecation-rules/private-feature-declarations */

// eslint-disable-next-line sw-deprecation-rules/private-feature-declarations
HeyFrame.Module.register('sw-import-export', {
    type: 'core',
    name: 'ImportExport',
    title: 'sw-import-export.general.mainMenuItemGeneral',
    description: 'sw-import-export.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#9AA8B5',
    icon: 'regular-cog',
    entity: 'import_export_profile',
    routePrefixPath: 'sw/import-export',

    routes: {
        index: {
            component: 'sw-import-export',
            path: 'index',
            meta: {
                parentPath: 'sw.settings.index',
                privilege: 'system.import_export',
            },
            redirect: {
                name: 'sw.import.export.index.import',
            },

            children: {
                import: {
                    component: 'sw-import-export-view-import',
                    path: 'import',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'system.import_export',
                    },
                },
                export: {
                    component: 'sw-import-export-view-export',
                    path: 'export',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'system.import_export',
                    },
                },
                profiles: {
                    component: 'sw-import-export-view-profiles',
                    path: 'profiles',
                    meta: {
                        parentPath: 'sw.settings.index',
                        privilege: 'system.import_export',
                    },
                },
            },
        },
    },

    settingsItem: {
        group: 'automation',
        to: 'sw.import.export.index',
        icon: 'regular-database',
        privilege: 'system.import_export',
    },
});
