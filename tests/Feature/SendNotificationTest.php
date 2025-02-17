<?php

namespace Modules\TelegramApi\tests\Feature;

use Illuminate\Support\Facades\Log;
use Modules\SystemBase\tests\TestCase;
use Modules\TelegramApi\app\Services\Notification\Channels\Telegram;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Models\NotificationEvent;
use Modules\WebsiteBase\app\Models\User;
use Modules\WebsiteBase\app\Services\NotificationEventService;

class SendNotificationTest extends TestCase
{
    protected ?User $telegramUser = null;

    /**
     * @return bool
     */
    public function prepareUserForTelegram(): bool
    {
        if ($this->telegramUser) {
            return true;
        }

        if ($user = app(User::class)->frontendItems()->inRandomOrder()->first()) {
            if ($telegramReceiverID = env('TESTING_TELEGRAM_RECEIVER_ID', '')) {
                Log::debug(sprintf("Preparing user for telegram: %s", $user->name));
                $user->setExtraAttribute('telegram_id', $telegramReceiverID);
                $user->setExtraAttribute('use_telegram', true);
                $user->setExtraAttribute(ExtraAttributeModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS, [Telegram::name]);

                // save it
                if ($user->save()) {
                    $this->telegramUser = $user;
                    return true;
                } else {
                    $this->assertTrue(false, "Failed toi save user with telegram id");
                }
            } else {
                $this->assertTrue(false, "Missing env TESTING_TELEGRAM_RECEIVER_ID");
            }
        } else {
            $this->assertTrue(false, "No valid user found");
        }
        return false;
    }

    // @todo: repair
    ///**
    // * 1) Make sure there is a user with valid telegram id
    // * 2) Send event concern to this user
    // *
    // *
    // * @return void
    // * @throws ContainerExceptionInterface
    // * @throws NotFoundExceptionInterface
    // * @throws TelegramSDKException
    // */
    //public function test_send_telegram()
    //{
    //    $this->prepareUserForTelegram();
    //
    //    $validatedData = ['user' => $this->telegramUser];
    //    Log::debug(sprintf("Try to send telegram message to user %s:%s ...", $this->telegramUser->id,
    //        $this->telegramUser->name));
    //    /** @var SendNotificationService $sendNotificationService */
    //    $sendNotificationService = app(SendNotificationService::class);
    //    $result = $sendNotificationService->sendNotificationConcern('remember_user_login_data',
    //        $validatedData['user'], ['contactData' => $validatedData]);
    //
    //    $this->assertTrue($result);
    //}

    /**
     * 1) Make sure there is a user with valid telegram id
     * 2) Send event notification to both users
     *
     * @return void
     */
    public function test_launch_notification_event()
    {
        $this->prepareUserForTelegram();

        if ($notificationEvent = NotificationEvent::with([])->where('name', 'Send User Login Data')->first()) {
            /** @var NotificationEventService $service */
            $service = app(NotificationEventService::class);
            $result = $service->launch($notificationEvent->getKey(), [$this->telegramUser->id]);

            $this->assertTrue($result);
        } else {
            $this->fail("Notification Event not found.");
        }
    }
}
