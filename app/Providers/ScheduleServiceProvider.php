<?php

namespace Modules\TelegramApi\app\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\SystemBase\app\Providers\Base\ScheduleBaseServiceProvider;
use Modules\TelegramApi\app\Services\TelegramApiService;
use Modules\TelegramApi\app\Services\TelegramService;

class ScheduleServiceProvider extends ScheduleBaseServiceProvider
{
    protected function bootEnabledSchedule(Schedule $schedule): void
    {
        /**
         * Update all telegram bots and save their groups,channels and users.
         * Additionally, adjust the env var TELEGRAM_BOTS_UPDATE_CACHE_TTL to minimize traffic.
         */
        $schedule->call(function () {

            /** @var TelegramApiService $telegramApiService */
            $telegramApiService = app(TelegramApiService::class);
            $telegramApiService->apiUpdateBotData();

        })->everyFiveMinutes();

        /**
         * Update all telegram bots and save their groups,channels and users.
         * Additionally, adjust the env var TELEGRAM_BOTS_UPDATE_CACHE_TTL to minimize traffic.
         */
        $schedule->call(function () {

            /** @var TelegramService $telegramService */
            $telegramService = app(TelegramService::class);
            $telegramService->ensureUsersByAllTelegramIdentities();

        })->everyTwoHours();

    }

}
