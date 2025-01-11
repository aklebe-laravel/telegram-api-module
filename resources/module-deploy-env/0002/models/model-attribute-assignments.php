<?php

use Modules\WebsiteBase\app\Models\ModelAttribute;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;

return [
    // class of eloquent model
    'model'   => ModelAttributeAssignment::class,
    // update data if exists and data differ (default false)
    'update'  => true,
    // columns to check if data already exists (AND WHERE)
    'uniques' => ['model', 'model_attribute_id'],
    // data rows itself
    'data'    => [
        [
            'model'              => 'App\Models\User',
            'model_attribute_id' => ModelAttribute::with([])->where('code', '=', 'telegram_id')->first()->getKey(),
            'attribute_type'     => 'string',
            'attribute_input'    => 'telegram-api::user_telegram_info',
            'description'        => 'Telegram ID',
            'form_position'      => '991',
            'form_css'           => 'col-12 col-md-6',
        ],
        [
            'model'              => 'App\Models\User',
            'model_attribute_id' => ModelAttribute::with([])->where('code', '=', 'use_telegram')->first()->getKey(),
            'attribute_type'     => 'integer',
            'attribute_input'    => 'switch',
            'description'        => 'Use Telegram',
            'form_position'      => '990',
            'form_css'           => 'col-12 col-md-6',
        ],
    ],
];

