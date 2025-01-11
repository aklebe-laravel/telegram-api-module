<?php

namespace Modules\TelegramApi\app\Providers;

use Modules\SystemBase\app\Providers\Base\ModuleBaseServiceProvider;
use Modules\TelegramApi\app\Console\EnsureUsers;
use Modules\TelegramApi\app\Console\TelegramGetUpdates;
use Modules\TelegramApi\app\Services\Notification\Channels\Telegram;
use Modules\TelegramApi\app\Services\TelegramApiService;
use Modules\TelegramApi\app\Services\TelegramService;

class TelegramApiServiceProvider extends ModuleBaseServiceProvider
{
    /**
     * @var string $moduleName
     */
    protected string $moduleName = 'TelegramApi';

    /**
     * @var string $moduleNameLower
     */
    protected string $moduleNameLower = 'telegram-api';

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        $this->commands([
            TelegramGetUpdates::class,
            EnsureUsers::class,
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(TelegramService::class);
        $this->app->singleton(TelegramApiService::class);
        $this->app->singleton(Telegram::class);

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ScheduleServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

}
