<?php

return [
    // class of eloquent model
    "model"     => \Modules\Acl\app\Models\AclGroup::class,
    // update data if exists and data differ (default false)
    "update"    => false,
    // columns to check if data already exists (AND WHERE)
    "uniques"   => ["name"],
    // relations to update/create
    "relations" => [
        "res" => [
            // relation method which have to exists
            "method" => "aclResources",
            // column(s) to find specific #sync_relations items below
            "columns" => "code",
            // delete items if not listed here (default: false)
            "delete" => false,
        ],
    ],
    // data rows itself
    "data"      => [
        [
            "name"            => "Telegram Channels",
            "description"     => "Telegram Channels",
            "#sync_relations" => [
                "res" => [
                    "telegram-channel"
                ]
            ]
        ],
        [
            "name"            => "Telegram Groups",
            "description"     => "Telegram Groups",
            "#sync_relations" => [
                "res" => [
                    "telegram-group"
                ]
            ]
        ],
    ]
];
