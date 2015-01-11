<?php
return [
    'processes' => [
        'ginger-message-sqlconnectorgingertypegingertestsourcetartikelcollection-collect-data' => [
            'id' => 'ginger-message-sqlconnectorgingertypegingertestsourcetartikelcollection-collect-data',
            'name' => 'Linear Collect TartikelCollection ',
            'processType' => 'linear_messaging',
            'startMessage' => [
                'messageType' => 'collect-data',
                __DIR__ . 'Type' => 'SqlConnector\\DataType\\GingerTestSource\\TartikelCollection',
            ],
            'tasks' => [
                0 => [
                    'task_type' => 'collect_data',
                    'source' => 'sqlconnector:::ginger_test_source',
                    __DIR__ . '_type' => 'SqlConnector\\DataType\\GingerTestSource\\TartikelCollection',
                    'id' => 1,
                ],
                1 => [
                    'task_type' => 'manipulate_payload',
                    'manipulation_script' => 'manipulate-products.php',
                    'id' => 2,
                ],
            ],
        ],
    ],
    'available_ginger_types' => [
        [
            'value' => 'SqlConnector\\DataType\\GingerTestSource\\Tartikel',
            'label' => 'Tartikel Testsource',
            'properties' => [
                'kArtikelId' => [
                    'value' => 'Ginger\\Type\\Integer',
                    'label' => 'Integer',
                    'properties' => [],
                    'native_type' => 'integer',
                ],
                'cName' => [
                    'value' => 'Ginger\\Type\\String',
                    'label' => 'String',
                    'properties' => [],
                    'native_type' => 'string',
                ],
                'cBeschreibung' => [
                    'value' => 'Ginger\\Type\\String',
                    'label' => 'String',
                    'properties' => [],
                    'native_type' => 'string',
                ],
                'kKategorieId' => [
                    'value' => 'Ginger\\Type\\Integer',
                    'label' => 'Integer',
                    'properties' => [],
                    'native_type' => 'integer',
                ],
            ],
            'native_type' => 'dictionary',
        ],
        [
            'value' => 'SqlConnector\\DataType\\GingerTestSource\\TartikelCollection',
            'label' => 'Tartikel Test List',
            'properties' => [
                'item' => [
                    'value' => 'SqlConnector\\DataType\\GingerTestSource\\Tartikel',
                    'label' => 'DB ginger_test_source.tartikel',
                    'properties' => [
                        'kArtikelId' => [
                            'value' => 'Ginger\\Type\\Integer',
                            'label' => 'Integer',
                            'properties' => [],
                            'native_type' => 'integer',
                        ],
                        'cName' => [
                            'value' => 'Ginger\\Type\\String',
                            'label' => 'String',
                            'properties' => [],
                            'native_type' => 'string',
                        ],
                        'cBeschreibung' => [
                            'value' => 'Ginger\\Type\\String',
                            'label' => 'String',
                            'properties' => [],
                            'native_type' => 'string',
                        ],
                        'kKategorieId' => [
                            'value' => 'Ginger\\Type\\Integer',
                            'label' => 'Integer',
                            'properties' => [],
                            'native_type' => 'integer',
                        ],
                    ],
                    'native_type' => 'dictionary',
                ],
            ],
            'native_type' => 'collection',
        ],
    ],
    'available_task_types' => [
        'collect_data',
        'process_data',
        'manipulate_payload',
        'run_sub_process',
    ],
    'available_manipulation_scripts' => [
        'manipulate-products.php',
    ],
    'connectors' => [
        'sqlconnector:::ginger_test_source' => [
            'allowed_messages' => [
                0 => 'collect-data',
            ],
            'allowed_types' => [
                0 => 'SqlConnector\\DataType\\GingerTestSource\\Tartikel',
                1 => 'SqlConnector\\DataType\\GingerTestSource\\TartikelCollection',
            ],
            'metadata' => [
                'identifier' => true,
            ],
            'dbal_connection' => 'ginger_test_source',
            'ui_metadata_key' => 'SqlconnectorMetadata',
        ],
    ],
];