<?php

declare(strict_types=1);

return [
    'menu' => [
        [
            'slug'       => 'agencies',
            'label'      => 'Agencies',
            'icon'       => '🏢',
            'href'       => '/agencies',
            'position'   => 40,
            'permission' => 'agency:view',
        ],
    ],

    'widgets' => [
        [
            'id'    => 'agencies',
            'area'  => 'right-4',
            'title' => 'Agencies',
            'api'   => '/api/v1/agencies',
        ],
    ],

    'detail_tabs' => [
        [
            'slug'       => 'overview',
            'label'      => 'Overview',
            'icon'       => '👁️',
            'api'        => '/api/v1/agencies/{id}',
            'position'   => 10,
            'permission' => 'agency:view',
        ],
        [
            'slug'       => 'units',
            'label'      => 'Units',
            'icon'       => '🏛️',
            'api'        => '/api/v1/agencies/{id}/units',
            'position'   => 20,
            'permission' => 'unit:view',
        ],
        [
            'slug'       => 'companies',
            'label'      => 'Companies',
            'icon'       => '🏢',
            'api'        => '/api/v1/agencies/{id}/companies',
            'position'   => 25,
            'permission' => 'agency:view',
        ],
        [
            'slug'       => 'jurisdictions',
            'label'      => 'Jurisdictions',
            'icon'       => '🗺️',
            'api'        => '/api/v1/agencies/{id}/jurisdictions',
            'position'   => 30,
            'permission' => 'jurisdiction:view',
        ],
        [
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/agencies/{id}/activity-logs',
            'position'   => 40,
            'permission' => 'agency:view',
        ],
    ],

    'rbac' => [
        'permissions' => [
            'agency:view', 'agency:create', 'agency:edit', 'agency:delete',
            'unit:view',   'unit:create',   'unit:edit',   'unit:delete',
            'jurisdiction:view', 'jurisdiction:create', 'jurisdiction:edit', 'jurisdiction:delete',
        ],
        'roles' => [
            ['name' => 'agency',              'label' => 'Agency',
             'permissions' => ['agency:view']],
            ['name' => 'agency-unit-admin',   'label' => 'Agency Unit Admin',
             'permissions' => ['agency:view', 'unit:*', 'jurisdiction:*']],
            ['name' => 'agency-admin',        'label' => 'Agency Admin',
             'permissions' => ['agency:*', 'unit:*', 'jurisdiction:*']],
        ],
        'grants' => [
            'staff' => ['agency:view', 'unit:view', 'jurisdiction:view'],
        ],
    ],
];
