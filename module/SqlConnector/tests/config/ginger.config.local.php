<?php
return [
    'system_config_dir' => __DIR__,
    'ginger' => [
        'node_name' => 'localhost',
        'plugins' => [],
        'processes' => [],
        'channels' => [
            'local' => [
                'targets' => [
                    0 => 'localhost',
                ],
                'utils' => [],
            ],
        ],
        'connectors' => [
            'sqlconnector:::ginger_test_users' => [
                'name' => 'Ginger Test Source DB',
                'allowed_messages' => [
                    0 => 'collect-data',
                ],
                'allowed_types' => [
                    0 => 'SqlConnectorTest\DataType\TestUser',
                    1 => 'SqlConnectorTest\DataType\TestUserCollection',
                ],
                'metadata' => [
                    'identifier' => true,
                ],
                'dbal_connection' => 'ginger_test_source',
                'table' => 'users',
                'ui_metadata_key' => 'SqlconnectorMetadata',
            ],
        ]
    ],
    'sqlconnector' => [
        'connections' => [
            'ginger_test_source' => [
                'driver' => 'pdo_sqlite',
                'memory' => true
            ]
        ]
    ]
];