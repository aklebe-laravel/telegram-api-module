<?php
return [
    'telegram'            => [
        'login' => [
            'delete' => [
                'title'   => 'Delete Telegram Connection',
                'content' => 'ask_delete_telegram_connection',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'cancel',
                    'telegram-delete-me',
                ],
            ],
        ],
    ],
];
