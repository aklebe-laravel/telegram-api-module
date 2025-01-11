<?php

namespace Modules\TelegramApi\app\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\SystemBase\app\Events\Livewire\BaseComponentActionCalled;
use Modules\TelegramApi\app\Listeners\FinalFormElements;
use Modules\WebsiteBase\app\Events\ValidNotificationChannel;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        BaseComponentActionCalled::class => [
            \Modules\TelegramApi\app\Listeners\BaseComponentActionCalled::class,
        ],
        \Modules\Form\app\Events\FinalFormElements::class => [
            FinalFormElements::class
        ],
        ValidNotificationChannel::class                 => [
            \Modules\TelegramApi\app\Listeners\ValidNotificationChannel::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
