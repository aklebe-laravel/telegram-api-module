<?php
return [
    'telegram' => [
        'login' => [
            'delete' => [
                'title'   => 'Delete Telegram Connection',
                'content' => 'ask_delete_telegram_connection',
                // constant names from defaultActions[] or closure
                'actions' => [
                    'system-base::cancel',
                    'telegram-api::telegram-delete-me',
                ],
            ],
        ],
    ],
];
