<?php

namespace Modules\TelegramApi\app\Listeners;

use Modules\Form\app\Events\FinalFormElements as FinalFormElementsEvent;
use Modules\Market\app\Forms\UserProfile;
use Modules\WebsiteBase\app\Services\WebsiteService;

class FinalFormElements
{
    public function handle(FinalFormElementsEvent $event): void
    {
        switch (true) {
            case $event->form instanceof UserProfile:
                app(WebsiteService::class)->provideMessageBoxButtons('telegram');

                break;

            default:
                break;
        }
    }
}