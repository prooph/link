<?php
return [
    'processes' => [
        'processing-message-sqlconnectorprocessingtypeprocessingtestsourcetartikelcollection-collect-data' => [
            'id' => 'processing-message-sqlconnectorprocessingtypeprocessingtestsourcetartikelcollection-collect-data',
            'name' => 'Linear Collect TartikelCollection ',
            'processType' => 'linear_messaging',
            'startMessage' => [
                'messageType' => 'collect-data',
                __DIR__ . 'Type' => 'SqlConnector\\DataType\\Prooph\ProcessingTestSource\\TartikelCollection',
            ],
            'tasks' => [
                0 => [
                    'task_type' => 'collect_data',
                    'source' => 'sqlconnector:::processing_test_source',
                    __DIR__ . '_type' => 'SqlConnector\\DataType\\Prooph\ProcessingTestSource\\TartikelCollection',
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
    'available_processing_types' => [
        [
            'value' => 'SqlConnector\\DataType\\Prooph\ProcessingTestSource\\Tartikel',
            'label' => 'Tartikel Testsource',
            'properties' => [
                'kArtikelId' => [
                    'value' => 'Prooph\\Processing\\Type\\Integer',
                    'label' => 'Integer',
                    'properties' => [],
                    'native_type' => 'integer',
                ],
                'cName' => [
                    'value' => 'Prooph\\Processing\\Type\\String',
                    'label' => 'String',
                    'properties' => [],
                    'native_type' => 'string',
                ],
                'cBeschreibung' => [
                    'value' => 'Prooph\\Processing\\Type\\String',
                    'label' => 'String',
                    'properties' => [],
                    'native_type' => 'string',
                ],
                'kKategorieId' => [
                    'value' => 'Prooph\\Processing\\Type\\Integer',
                    'label' => 'Integer',
                    'properties' => [],
                    'native_type' => 'integer',
                ],
            ],
            'native_type' => 'dictionary',
        ],
        [
            'value' => 'SqlConnector\\DataType\\Prooph\ProcessingTestSource\\TartikelCollection',
            'label' => 'Tartikel Test List',
            'properties' => [
                'item' => [
                    'value' => 'SqlConnector\\DataType\\Prooph\ProcessingTestSource\\Tartikel',
                    'label' => 'DB processing_test_source.tartikel',
                    'properties' => [
                        'kArtikelId' => [
                            'value' => 'Prooph\\Processing\\Type\\Integer',
                            'label' => 'Integer',
                            'properties' => [],
                            'native_type' => 'integer',
                        ],
                        'cName' => [
                            'value' => 'Prooph\\Processing\\Type\\String',
                            'label' => 'String',
                            'properties' => [],
                            'native_type' => 'string',
                        ],
                        'cBeschreibung' => [
                            'value' => 'Prooph\\Processing\\Type\\String',
                            'label' => 'String',
                            'properties' => [],
                            'native_type' => 'string',
                        ],
                        'kKategorieId' => [
                            'value' => 'Prooph\\Processing\\Type\\Integer',
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
        'sqlconnector:::processing_test_source' => [
            'allowed_messages' => [
                0 => 'collect-data',
            ],
            'allowed_types' => [
                0 => 'SqlConnector\\DataType\\Prooph\ProcessingTestSource\\Tartikel',
                1 => 'SqlConnector\\DataType\\Prooph\ProcessingTestSource\\TartikelCollection',
            ],
            'metadata' => [
                'identifier' => true,
            ],
            'dbal_connection' => 'processing_test_source',
            'ui_metadata_key' => 'SqlconnectorMetadata',
        ],
    ],
];