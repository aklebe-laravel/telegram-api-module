<?php

namespace Modules\TelegramApi\app\Providers;

use Modules\SystemBase\app\Providers\Base\ModuleBaseServiceProvider;
use Modules\TelegramApi\app\Console\TelegramGetUpdates;

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
            TelegramGetUpdates::class
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

        $this->app->register(RouteServiceProvider::class);
        $this->app->register(ScheduleServiceProvider::class);
    }

}
