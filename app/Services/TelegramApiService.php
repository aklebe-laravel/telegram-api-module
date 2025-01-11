<?php

namespace Modules\TelegramApi\app\Services;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\TelegramApi\app\Models\TelegramIdentity;
use Modules\WebsiteBase\app\Services\ConfigService;
use Telegram\Bot\Api;
use Telegram\Bot\Laravel\Facades\Telegram;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\Update;
use Throwable;

class TelegramApiService extends BaseService
{
    /**
     *
     *
     * @var array
     */
    protected array $telegramEntitiesFound = [];

    /**
     * @var ConfigService
     */
    protected ConfigService $configService;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->configService = app(ConfigService::class);
    }

    /**
     * - Run for all defined bots in config('telegram.bots')
     * - Read Bot info (getMe)
     * - Read all chat groups, channels and users
     * - write all to db
     *
     * @return void
     * @todo: cache
     */
    public function apiUpdateBotData(): void
    {
        if (!app(TelegramService::class)->isTelegramEnabled()) {
            return;
        }

        try {

            $ttlDefault = config('system-base.cache.default_ttl', 1);
            $ttl = config('telegram.bots_update_ttl', $ttlDefault);
            Cache::remember('TELEGRAM_BOTS_UPDATE', $ttl, function () {

                foreach (config('telegram.bots') as $botName => $bot) {

                    // For each bot reset the founds.
                    // Gather new and old group chats and channels to set parent below.
                    $this->telegramEntitiesFound = [];

                    $botApi = Telegram::bot($botName);
                    if ($botApi->getWebhookInfo()->url) {
                        continue;
                    }

                    $response = $botApi->getMe();
                    $requestedBotId = $response->id;
                    $this->debug(sprintf("Updating telegram Bot '%s' (Bot ID: %s)", $botName, $requestedBotId));//, [$response]);

                    $response = $botApi->getUpdates();

                    foreach ($response as $update) {
                        $this->resolveUpdateObjects($update);
                    }

                    // update all entries just found with their finder
                    if ($this->telegramEntitiesFound) {
                        //
                        $this->debug(sprintf("%s telegram entities found.", count($this->telegramEntitiesFound)));

                        // get the bot ...
                        if ($requestedTelegramBot = TelegramIdentity::with([])
                            ->where('telegram_id', $requestedBotId)
                            ->first()
                        ) {
                            foreach (
                                TelegramIdentity::with([])
                                    ->whereIn('telegram_id', $this->telegramEntitiesFound)
                                    ->get() as $telegramUserFound
                            ) {
                                $telegramUserFound->finders()->syncWithoutDetaching($requestedTelegramBot);
                            }
                        }
                    }

                }

                return true;
            });

        } catch (Exception $ex) {
            $this->error("Exception: ".$ex->getMessage());
            $this->error($ex->getTraceAsString());
        }

    }

    /**
     * @param  Update  $update
     *
     * @return void
     */
    private function resolveUpdateObjects(Update $update): void
    {
        /** @var Message $message */
        foreach ($update->getMessage() as $messageName => $message) {

            switch ($messageName) {

                case 'new_chat_members':
                    // array of chat members?
                    break;

                // case 'new_chat_participant':
                case 'new_chat_member':
                case 'chat':
                case 'from':
                    if ($id = data_get($message, 'id')) {

                        if (!in_array($id, $this->telegramEntitiesFound)) {

                            if ($telegramIdentity = $this->collectUserFromUpdateObject($message)) {
                                $this->telegramEntitiesFound[] = $telegramIdentity->telegram_id;
                            }

                        }
                    }
                    break;

                // case 'left_chat_participant':
                case 'left_chat_member':
                    // remove this entity
                    break;
            }
        }

    }

    /**
     * 1) Create or update the TelegramIdentity for the given telegram user id
     * 2) Create the User model related to the TelegramIdentity
     *
     * @param  array  $telegramUser
     *
     * @return TelegramIdentity|null
     */
    private function collectUserFromUpdateObject(array $telegramUser): ?TelegramIdentity
    {
        $map = [
            'telegram_id'   => 'id',
            'type'          => 'type',
            'display_name'  => fn() => data_get($telegramUser, 'first_name', data_get($telegramUser, 'title', '???')),
            'username'      => 'username',
            'language_code' => 'language_code',
            'is_bot'        => fn() => data_get($telegramUser, 'is_bot', false),
        ];

        $modelData = [];
        // fill model data if field exists ...
        foreach ($map as $mapKey => $mapValue) {
            if (app('system_base')->isCallableClosure($mapValue)) {
                $modelData[$mapKey] = $mapValue();
            } else {
                if (isset($telegramUser[$mapValue])) {
                    $modelData[$mapKey] = $telegramUser[$mapValue];
                }
            }
        }

        // Create or update Telegram Identity and User related to this identity ...
        if ($u = app(TelegramService::class)->ensureTelegramUser($modelData)) {
            return $u['TelegramIdentity'];
        }

        return null;
    }

    /**
     * @param  string  $message
     * @param  string  $chatId
     * @param  array   $buttonContainer
     *
     * @return void
     */
    public function apiSendMessage(string $message, string $chatId, array $buttonContainer = []): void
    {
        $sendData = [
            'chat_id'                  => $chatId,
            'text'                     => $message,
            'parse_mode'               => 'HTML',
            'disable_notification'     => false,
            'disable_web_page_preview' => true,
        ];

        if ($buttonContainer) {
            $sendData['reply_markup'] = json_encode([
                'inline_keyboard' => $buttonContainer,
            ]);
        }

        try {
            $bot = $this->getDefaultBot();
            $bot->sendMessage($sendData);
        } catch (Throwable $ex) {
            $this->error("Exception: ".$ex->getMessage(), [__METHOD__]);
        }
    }

    /**
     * Get 1) default bot by core_configs and 2) by default in config/telegram.php
     *
     * @return string
     */
    public function getDefaultBotName(): string
    {
        return $this->configService->getValue('notification.channels.telegram.bot', Telegram::getConfig('default', ''));
    }

    /**
     * @return Api
     */
    public function getDefaultBot(): Api
    {
        return Telegram::bot($this->getDefaultBotName());
    }

    /**
     * Get groups from db.
     *
     * @param  string  $botName  from config "telegram.bots"
     * @param  array   $groupsAndChannels
     *
     * @return Collection|false
     */
    public function getBotGroups(string $botName = '', array $groupsAndChannels = [TelegramIdentity::TYPE_GROUP, TelegramIdentity::TYPE_CHANNEL]): Collection|false {
        if (!$botName) {
            if (!($botName = $this->getDefaultBotName())) {
                return false;
            }
        }

        // get the bot itself ...
        // $botApi = Telegram::bot($botName);
        if (!($botIdentity = TelegramIdentity::with([])->where('username', $botName)->first())) {
            return false;
        }

        // get chats from bot ...
        return $botIdentity->children->whereIn('type', $groupsAndChannels);
    }

    /**
     * subject and content will extra be rendered by Blade.
     *
     * @param  array{
     *     user:User,
     *     subject:string,
     *     content:string,
     *     view_path:string
     * }  $customContentData
     *
     * @return string
     */
    public function prepareTelegramMessage(array $customContentData): string
    {
        if ($customContentData['subject'] ?? null) {
            $customContentData['subject'] = trim(Blade::render($customContentData['subject'], $customContentData));
        }
        if ($customContentData['content'] ?? null) {
            $customContentData['content'] = trim(Blade::render($customContentData['content'], $customContentData));
        }
        // $viewPath = data_get($customContentData, 'view_path', 'telegram-api::telegram.default-message');
        $viewPath = ($customContentData['view_path'] ?? '') ?: 'telegram-api::telegram.default-message'; // data_get() not working here

        return view($viewPath, $customContentData);
    }

}