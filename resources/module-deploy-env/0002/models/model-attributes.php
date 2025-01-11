<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;

return [
    // class of eloquent model
    'model'   => ModelAttribute::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['code'], // Not ['module','code'] in this version!
    // data rows itself
    'data'    => [
        [
            'module'      => 'telegram-api',
            'code'        => 'telegram_id',
            'description' => 'Telegram ID',
        ],
        [
            'module'      => 'telegram-api',
            'code'        => 'use_telegram',
            'description' => 'Use telegram for communications',
        ],
    ],
];

