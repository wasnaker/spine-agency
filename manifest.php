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
            'id'    => 'agencies-items',
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
            'slug'       => 'activity',
            'label'      => 'Activity',
            'icon'       => '🕐',
            'api'        => '/api/v1/agencies/{id}/activity-logs',
            'position'   => 30,
            'permission' => 'agency:view',
        ],
    ],

    'rbac' => [
        'permissions' => [
            'agency:view', 'agency:create', 'agency:edit', 'agency:delete',
            'unit:view',   'unit:create',   'unit:edit',   'unit:delete',
        ],
        'roles' => [
            ['name' => 'agency',              'label' => 'Agency',
             'permissions' => ['agency:view']],
            ['name' => 'agency-unit-admin',   'label' => 'Agency Unit Admin',
             'permissions' => ['agency:view', 'unit:*']],
            ['name' => 'agency-admin',        'label' => 'Agency Admin',
             'permissions' => ['agency:*', 'unit:*']],
        ],
        'grants' => [
            'staff' => ['agency:view', 'unit:view'],
        ],
    ],
];
