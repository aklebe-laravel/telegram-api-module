<?php

namespace Modules\TelegramApi\app\Listeners;

use Modules\Form\app\Events\InitFormElements as InitFormElementsEvent;
use Modules\Form\app\Services\FormService;
use Modules\TelegramApi\app\Services\TelegramFormService;

class InitFormElements
{
    /**
     * @param  InitFormElementsEvent  $event
     *
     * @return void
     */
    public function handle(InitFormElementsEvent $event): void
    {
        /** @var FormService $formService */
        $formService = app(FormService::class);
        /** @var TelegramFormService $formService */
        $telegramFormService = app(TelegramFormService::class);

        $formService->registerFormElement('notification.channels.telegram.default_public_group', fn($x) => $telegramFormService::getFormElementTelegramGroup($x));
        $formService->registerFormElement('notification.channels.telegram.default_staff_group', fn($x) => $telegramFormService::getFormElementTelegramGroup($x));
    }
}