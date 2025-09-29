/**
 * @sw-package discovery
 */

HeyFrame.Service('privileges')
    .addPrivilegeMappingEntry({
        navigation: 'permissions',
        parent: 'catalogues',
        key: 'navigation',
        roles: {
            viewer: {
                privileges: [
                    'navigation:read',
                    'navigation_translation:read',
                    HeyFrame.Service('privileges').getPrivileges('media.viewer'),
                    'seo_url:read',
                    'tag:read',
                    'sales_channel:read',
                    'product:read',
                    'property_group_option:read',
                    'property_group:read',
                    'product_manufacturer:read',
                    'sales_channel_type:read',
                    HeyFrame.Service('privileges').getPrivileges('cms.viewer'),
                    'custom_field_set:read',
                    'custom_field:read',
                    'custom_field_set_relation:read',
                    'product_stream:read',
                    'currency:read',
                ],
                dependencies: [],
            },
            editor: {
                privileges: [
                    'navigation:update',
                    'media:delete',
                    'media_thumbnail:delete',
                    HeyFrame.Service('privileges').getPrivileges('media.creator'),
                    HeyFrame.Service('privileges').getPrivileges('cms.editor'),
                    'product_navigation:create',
                    'tag:create',
                    'navigation_tag:create',
                    'navigation_tag:delete',
                ],
                dependencies: [
                    'navigation.viewer',
                ],
            },
            creator: {
                privileges: [
                    'navigation:create',
                ],
                dependencies: [
                    'navigation.viewer',
                    'navigation.editor',
                ],
            },
            deleter: {
                privileges: [
                    'navigation:delete',
                ],
                dependencies: [
                    'navigation.viewer',
                ],
            },
        },
    })
    .addPrivilegeMappingEntry({
        navigation: 'permissions',
        parent: 'catalogues',
        key: 'landing_page',
        roles: {
            viewer: {
                privileges: [
                    'landing_page:read',
                    'landing_page_translation:read',
                    'landing_page_tag:read',
                    'landing_page_sales_channel:read',
                    HeyFrame.Service('privileges').getPrivileges('media.viewer'),
                    'tag:read',
                    'sales_channel:read',
                    'sales_channel_type:read',
                    HeyFrame.Service('privileges').getPrivileges('cms.viewer'),
                    'custom_field_set:read',
                    'custom_field:read',
                    'custom_field_set_relation:read',
                ],
                dependencies: [],
            },
            editor: {
                privileges: [
                    'landing_page:update',
                    'landing_page_translation:create',
                    'landing_page_translation:update',
                    HeyFrame.Service('privileges').getPrivileges('media.creator'),
                    HeyFrame.Service('privileges').getPrivileges('cms.editor'),
                    'tag:create',
                    'landing_page_tag:create',
                    'landing_page_tag:delete',
                    'landing_page_sales_channel:create',
                    'landing_page_sales_channel:delete',
                ],
                dependencies: [
                    'navigation.viewer',
                ],
            },
            creator: {
                privileges: [
                    'landing_page:create',
                ],
                dependencies: [
                    'landing_page.viewer',
                    'landing_page.editor',
                ],
            },
            deleter: {
                privileges: [
                    'landing_page:delete',
                ],
                dependencies: [
                    'landing_page.viewer',
                ],
            },
        },
    });
