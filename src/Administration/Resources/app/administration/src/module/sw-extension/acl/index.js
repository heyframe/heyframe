/**
 * @sw-package checkout
 */
HeyFrame.Service('privileges').addPrivilegeMappingEntry({
    category: 'additional_permissions',
    parent: null,
    key: 'system',
    roles: {
        extension_store: {
            privileges: [],
            dependencies: [
                'system.plugin_maintain',
            ],
        },
    },
});
