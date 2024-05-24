<?php

return [
    // class of eloquent model
    "model"   => \Modules\Acl\app\Models\AclResource::class,
    // update data if exists and data differ (default false)
    "update"  => false,
    // columns to check if data already exists (AND WHERE)
    "uniques" => ["code"],
    // data rows itself
    "data"    => [
        [
            "code"        => "telegram-channel",
            "name"        => "Telegram Channel",
            "description" => "Telegram Channel"
        ],
        [
            "code"        => "telegram-group",
            "name"        => "Telegram Group",
            "description" => "Telegram Group"
        ],
    ]
];

