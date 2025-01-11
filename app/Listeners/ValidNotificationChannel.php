<?php

namespace Modules\TelegramApi\app\Listeners;

use Modules\TelegramApi\app\Services\Notification\Channels\Telegram;
use Modules\WebsiteBase\app\Events\ValidNotificationChannel as ValidNotificationChannelAliasEvent;
use Modules\WebsiteBase\app\Services\SendNotificationService;

class ValidNotificationChannel
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @param  ValidNotificationChannelAliasEvent  $event
     *
     * @return void
     */
    public function handle(ValidNotificationChannelAliasEvent $event): void
    {
        $notificationService = app(SendNotificationService::class);

        // add telegram channel
        $channel = app(Telegram::class);
        if ($channel->isChannelValid()) {
            $notificationService->registerChannel($channel);
        }
    }
}
