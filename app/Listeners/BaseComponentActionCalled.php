<?php

namespace Modules\TelegramApi\app\Listeners;

use Illuminate\Support\Facades\Auth;
use Modules\SystemBase\app\Events\Livewire\BaseComponentActionCalled as SystemBaseBaseComponentActionCalledAlias;
use Modules\TelegramApi\app\Services\Notification\Channels\Telegram;
use Modules\TelegramApi\app\Services\TelegramService;
use Modules\WebsiteBase\app\Http\Livewire\Form\UserProfile;
use Modules\WebsiteBase\app\Models\User as UserModel;

class BaseComponentActionCalled
{
    public function handle(SystemBaseBaseComponentActionCalledAlias $event): void
    {
        if (!(($livewireForm = $event->baseComponent) instanceof UserProfile)) {
            return;
        }

        switch ($event->action) {
            case  'telegram-assign-me':
                $requestUser = $event->itemId;

                /** @var TelegramService $telegramService */
                $telegramService = app(TelegramService::class);

                if (!Auth::check() || !($user = Auth::user())) {
                    $livewireForm->addErrorMessage('No user logged in. Session expired?');

                    return;
                }

                $telegramIdentityModelData = [
                    'telegram_id'  => data_get($requestUser, 'id'),
                    'display_name' => data_get($requestUser, 'first_name'),
                    'username'     => data_get($requestUser, 'username'),
                    // 'img' => data_get($requestUser, 'photo_url'),
                ];

                // Check telegram id already in used ...
                if ($foundUserId = $telegramService->findUserByTelegramId($telegramIdentityModelData['telegram_id'])) {

                    // The found telegram id is not owned by the current user ...
                    if ($foundUserId != $user->getKey()) {
                        $livewireForm->addErrorMessage("Es gibt bereits einen Benutzer, der diesem Telegram-Account zugewiesen wurde.");

                        return;
                    }

                }

                // Create Telegram Identity only, not the user model!
                if ($telegramIdentityFound = $telegramService->ensureTelegramIdentity($telegramIdentityModelData)) {

                    // force channel to telegram
                    $telegramService->setUserPreferredChannel($user, $telegramIdentityFound);

                    // save it
                    if ($user->save()) {
                        $livewireForm->addSuccessMessage(sprintf("Die Telegram-Verknüpfung für '%s' wurde erfolgreich angelegt ", data_get($requestUser, 'first_name')));
                        // reopen form to reload new user data
                        $livewireForm->openForm($user->getKey());

                        return;
                    }

                }

                $livewireForm->addErrorMessage('Die Telegram-Verknüpfung konnte leider nicht angelegt werden.');
                // // reopen form to reset telegram widget - not working
                // $livewireForm->openForm($user->getKey());
                break;

            case 'telegram-delete-me':

                /** @var UserModel $user */
                if (!($user = app(UserModel::class)->with([])->whereId($event->itemId)->first())) {
                    $livewireForm->addErrorMessage(__('User not found.'));

                    return;
                }

                // don't delete if it's a fake email
                if ($user->hasFakeEmail()) {
                    $livewireForm->addErrorMessage(__('unable_to_delete_users_last_identity'));

                    return;
                }

                // remove extra attribute: telegram_id
                $user->setExtraAttribute('telegram_id', null);
                $user->setExtraAttribute('use_telegram', false);
                // remove preferred channel when its telegram
                $prefChannels = $user->getPreferredNotificationChannels();
                if (in_array(Telegram::name, $prefChannels)) {
                    if (($key = array_search(Telegram::name, $prefChannels)) !== false) {
                        unset($prefChannels[$key]);
                        $user->setExtraAttribute(UserModel::ATTR_NOTIFICATION_CHANNELS, $prefChannels);
                    }
                }
                // save it
                if ($user->save()) {
                    $livewireForm->addSuccessMessage("Die Telegram-Verknüpfung wurde erfolgreich entfernt ");
                    // reopen form to reload new user data
                    $livewireForm->openForm($user->getKey());
                }
                break;
        }

    }
}