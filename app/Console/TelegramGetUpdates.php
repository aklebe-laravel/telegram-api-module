<?php

namespace Modules\TelegramApi\app\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Modules\TelegramApi\app\Services\TelegramApiService;
use Symfony\Component\Console\Command\Command as CommandResult;

class TelegramGetUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram-api:get-updates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get updates from bots and their latest identities (groups, channels, members) by using api.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Cache::forget('TELEGRAM_BOTS_UPDATE');

        /** @var TelegramApiService $telegramApiService */
        $telegramApiService = app(TelegramApiService::class);
        $telegramApiService->apiUpdateBotData();

        return CommandResult::SUCCESS;
    }

}
