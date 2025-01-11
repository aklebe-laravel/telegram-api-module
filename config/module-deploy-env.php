<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config of deployments. Can be updated by adding new identifiers.
    | See module DeployEnv README.md
    |--------------------------------------------------------------------------
    */

    'deployments' => [
        // Identifier to remember this deployment was already done.
        '0001' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'acl-resources.php',
                    'acl-groups.php',
                ],
            ],
        ],
        '0002' => [
            [
                'cmd'     => 'models',
                'sources' => [
                    'core-config.php',
                    'model-attributes.php',
                    'model-attribute-assignments.php',
                    'view-templates.php',
                    'notification-templates.php',
                    'notification-concerns.php',
                ],
            ],
        ],
    ],
];
