<?php

namespace Modules\TelegramApi\app\Services\Notification\Channels;

use Modules\TelegramApi\app\Services\TelegramApiService;
use Modules\TelegramApi\app\Services\TelegramService;
use Modules\WebsiteBase\app\Jobs\NotificationEventProcess;
use Modules\WebsiteBase\app\Models\NotificationConcern as NotificationConcernModel;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Services\Notification\Channels\BaseChannel;

class Telegram extends BaseChannel
{
    /**
     * @var string
     */
    const string name = 'telegram';

    /**
     * @var TelegramService
     */
    protected TelegramService $telegramService;

    /**
     * @var TelegramApiService
     */
    protected TelegramApiService $telegramApiService;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->telegramService = app(TelegramService::class);
        $this->telegramApiService = app(TelegramApiService::class);
    }

    /**
     * @return void
     */
    public function initChannel(): void
    {

    }

    /**
     * @return bool
     */
    public function isChannelValid(): bool
    {
        if (!$this->telegramService->isTelegramEnabled()) {
            return false;
        }

        if (!$this->websiteBaseConfig->getValue('notification.channels.telegram.enabled', false)) {
            $this->warning("Telegram disabled.", [__METHOD__]);

            return false;
        }
        return true;
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function canNotifyUser(User $user): bool
    {
        $useTelegram = $user->getExtraAttribute('use_telegram');
        $telegramId = $user->getExtraAttribute('telegram_id');

        return ($useTelegram && $telegramId);
    }

    /**
     * @param  User  $user
     *
     * @return bool
     */
    public function beforeSend(User $user): bool
    {
        if (!parent::beforeSend($user)) {
            return false;
        }

        if ($this->websiteBaseConfig->getValue('notification.simulate', false)) {
            $this->info("Simulating notification: ", [self::name, $user->name, $user->email]);

            return false;
        }

        return true;
    }

    /**
     * @param  User   $user
     * @param  array  $options
     *
     * @return bool
     */
    public function sendMessage(User $user, array $options = []): bool
    {
        /** @var NotificationEventProcess $notificationEventProcess */
        $notificationEventProcess = $options['notification_event_process'];


        $this->debug(sprintf("Sending telegram message to user: %s", $user->name), [__METHOD__]);

        if ($this->websiteBaseConfig->getValue('notification.simulate', false)) {
            $this->info("Simulating notification: ", [self::name, $user->name]);

            return true;
        }

        $telegramApiService = app(TelegramApiService::class);
        $d = [
            'view_path' => data_get($this->event->event_data ?? [], 'view_path', ''),
            'user'      => $user,
            'subject'   => $notificationEventProcess->event->getSubject(self::name),
            'content'   => $notificationEventProcess->event->getContent(self::name),
        ];
        if ($message = $telegramApiService->prepareTelegramMessage(app('system_base')->arrayMergeRecursiveDistinct($notificationEventProcess->customContentData, $d))) {
            $buttonsCode = data_get($notificationEventProcess->event->event_data ?? [], 'buttons');
            $buttons = $buttonsCode ? config('combined-module-telegram-api.button_groups.'.$buttonsCode, []) : [];

            if ($this->websiteBaseConfig->getValue('notification.simulate', false)) {
                $this->info("Simulating notification: ", [self::name, $user->name, $buttonsCode]);

                return true;
            } else {
                $telegramApiService->apiSendMessage($message, $user->getExtraAttribute('telegram_id'), $buttons);

                return true;
            }

        } else {
            $this->error(sprintf("Empty message for event: %s", $notificationEventProcess->event->getKey()), [__METHOD__]);

            return false;
        }
    }

    /**
     * @param  User                      $user
     * @param  NotificationConcernModel  $concern
     * @param  array                     $viewData
     * @param  array                     $tags
     * @param  array                     $metaData
     *
     * @return bool
     */
    public function sendNotificationConcern(User $user, NotificationConcernModel $concern, array $viewData = [], array $tags = [], array $metaData = []): bool
    {
        $d = [
            // 'view_path' => '...',
            'user'    => $user,
            'subject' => $concern->getSubject(),
            'content' => $concern->getContent(),
        ];
        if ($message = $this->telegramApiService->prepareTelegramMessage(app('system_base')->arrayMergeRecursiveDistinct($viewData, $d))) {
            $buttons = config('combined-module-telegram-api.button_groups.website_link', []);
            $this->telegramApiService->apiSendMessage($message, $user->getExtraAttribute('telegram_id'), $buttons);
            return true;
        } else {
            $this->error(sprintf("Empty telegram message for notification concern: %s",
                $concern->getKey()), [__METHOD__]);
            return false;
        }
    }

}