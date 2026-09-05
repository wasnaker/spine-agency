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
            // Platform/agency (agency:view) ATAU surveyor (register lintas dinas).
            'permission' => 'agency:view|agency:surveyor-register',
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
            'slug'       => 'registrations',
            'label'      => 'Surveyor Regs',
            'icon'       => '📝',
            'api'        => '/api/v1/agencies/{id}/surveyor-registrations',
            'position'   => 26,
            'permission' => 'agency:approve-surveyor-registration',
        ],
        [
            'slug'       => 'staffs',
            'label'      => 'Staff',
            'icon'       => '👥',
            'api'        => '/api/v1/agencies/{id}/staffs',
            'position'   => 27,
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

    'settings' => [
        [
            'slug'     => 'agency',
            'label'    => 'Agency',
            'icon'     => '🏢',
            'position' => 50,
            'fields'   => [
                [
                    'key'     => 'agency_start_number',
                    'label'   => 'Start Number',
                    'type'    => 'number',
                    'default' => '10101',
                ],
                [
                    'key'     => 'agency_code_length',
                    'label'   => 'Code Length',
                    'type'    => 'number',
                    'default' => '4',
                ],
            ],
        ],
    ],

    'rbac' => [
        'permissions' => [
            'agency:view', 'agency:create', 'agency:edit', 'agency:delete',
            'unit:view',   'unit:create',   'unit:edit',   'unit:delete',
            'jurisdiction:view', 'jurisdiction:create', 'jurisdiction:edit', 'jurisdiction:delete',
            'agency:approve-surveyor-registration',
            'agency:surveyor-register',
            'lhp:view', 'lhp:create', 'lhp:edit', 'lhp:delete',
            'suket:tandatangan',
            'pengawas:assign',
            'pengawas:view',
        ],
        'roles' => [
            ['name' => 'agency',              'label' => 'Agency',
             'permissions' => ['agency:view']],
            ['name' => 'agency-unit-admin',   'label' => 'Agency Unit Admin',
             'permissions' => ['agency:view', 'unit:*', 'jurisdiction:*', 'pengawas:assign', 'pengawas:view', 'customer:view']],
            ['name' => 'admin-dinas',           'label' => 'Admin Dinas',
             'permissions' => ['agency:*', 'unit:*', 'jurisdiction:*',
                'agency:approve-surveyor-registration', 'pengawas:assign', 'pengawas:view', 'customer:view']],
            ['name' => 'pengawas',            'label' => 'Pengawas',
             'permissions' => ['agency:view', 'customer:view', 'lhp:view', 'lhp:create', 'lhp:edit', 'lhp:delete',
                'laporan-pjk3:review', 'laporan-pjk3:terima', 'laporan-pjk3:tolak']],
            ['name' => 'pengawas-spesialis',  'label' => 'Pengawas Spesialis',
             'permissions' => ['agency:view', 'customer:view', 'lhp:view', 'lhp:create', 'lhp:edit', 'lhp:delete', 'suket:tandatangan',
                'laporan-pjk3:review', 'laporan-pjk3:terima', 'laporan-pjk3:tolak']],
        ],
        'grants' => [
            'staff' => ['agency:view', 'unit:view', 'jurisdiction:view'],
        ],
    ],
];
