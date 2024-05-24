<?php

/*
|--------------------------------------------------------------------------
| Config for telegram api.
| Can be extended and/or adjusted in any other module to add/overwrite settings.
| See README.md in module TelegramApi
|
| DO NOT TRANSLATE LIKE __('x') INC CONFIG FILES!
|--------------------------------------------------------------------------
*/

$_buttons = [
    'link_to_website'     => [
        'text' => 'Go To Website',
        'url'  => config('app.url'),
    ],
    'event_accept'        => [
        'text'          => 'Accept',
        'callback_data' => '/event accept ${{id}}'
    ],
    'event_decline'       => [
        'text'          => 'Decline',
        'callback_data' => '/event decline ${{id}}'
    ],
    'receive_event_again' => [
        'text'          => 'Receive again',
        'callback_data' => '/event receive ${{id}}'
    ],
    'link_to_event'       => [
        'text' => 'Go To Event (Auto Login)',
        'url'  => ''
    ],
];

return [
    'buttons'       => $_buttons,
    'button_groups' => [
        'website_link'  => [
            [
                $_buttons['link_to_website'],
            ],
        ],
        'event_confirm' => [
            [
                $_buttons['event_accept'],
                $_buttons['event_decline'],
            ],
            [
                $_buttons['receive_event_again'],
            ],
            [
                $_buttons['link_to_event'],
            ]
        ],
    ],
];
