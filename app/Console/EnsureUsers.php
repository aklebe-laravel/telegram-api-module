<?php

namespace Modules\TelegramApi\app\Console;

use Illuminate\Console\Command;
use Modules\TelegramApi\app\Services\TelegramService;
use Symfony\Component\Console\Command\Command as CommandResult;

class EnsureUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram-api:ensure-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating users for all non bot telegram entities not already exists. So one user is assigned per channel and group.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        /** @var TelegramService $telegramService */
        $telegramService = app(TelegramService::class);
        $telegramService->ensureUsersByAllTelegramIdentities();

        return CommandResult::SUCCESS;
    }

}
