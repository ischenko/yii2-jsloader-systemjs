<?php

return [
    'id' => 'systemjs-test',
    'basePath' => '@tests/../',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@runtime' => '@tests/_output'
    ],
    'components' => [
        'assetManager' => [
            'basePath' => '@runtime'
        ]
    ]
];
