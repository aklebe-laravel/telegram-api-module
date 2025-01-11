<?php

namespace Modules\TelegramApi\app\Listeners;

use Modules\Form\app\Events\FinalFormElements as FinalFormElementsEvent;
use Modules\Market\app\Forms\UserProfile;

class FinalFormElements
{
    public function handle(FinalFormElementsEvent $event): void
    {
        switch (true) {
            case $event->form instanceof UserProfile:
                $event->form->formLivewire->addMessageBoxButton('telegram-delete-me', 'telegram-api');
                break;

            default:
                break;
        }
    }
}