<?php

namespace Modules\TelegramApi\app\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Acl\app\Models\AclGroup;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\TelegramApi\app\Models\TelegramIdentity;
use Modules\TelegramApi\app\Services\Notification\Channels\Telegram;
use Modules\WebsiteBase\app\Models\Base\ExtraAttributeModel;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
use Modules\WebsiteBase\app\Models\User;

class TelegramService extends BaseService
{
    /**
     * @return bool
     */
    public function isTelegramEnabled(): bool
    {
        return !!app('website_base_config')->getValue('channels.telegram.enabled', false);
    }

    /**
     * @return ModelAttributeAssignment|Model|null
     */
    public function getUserAttributeWithTelegramId(): ModelAttributeAssignment|Model|null
    {
        return ModelAttributeAssignment::with(['modelAttribute'])->where('model', '=', \App\Models\User::class)
            ->whereHas('modelAttribute', function ($query) {
                return $query->where('code', '=', 'telegram_id');
            })->first();
    }

    /**
     * @return array
     */
    public function findUsersHavingTelegramId(): array
    {
        if ($attr = $this->getUserAttributeWithTelegramId()) {
            if ($builder = DB::table(User::getAttributeTypeTableName($attr->attribute_type))
                ->where('model_attribute_assignment_id', '=', $attr->id)
            ) {
                // model_id is user id
                return $builder->pluck('model_id')->toArray();
            }
        }

        return [];
    }

    /**
     * @param  string  $telegramId
     *
     * @return int user id or 0
     */
    public function findUserByTelegramId(string $telegramId): int
    {
        if ($attr = $this->getUserAttributeWithTelegramId()) {
            if ($builder = DB::table(User::getAttributeTypeTableName($attr->attribute_type))
                ->where('model_attribute_assignment_id', '=', $attr->id)
                ->where('value', '=', $telegramId)
            ) {
                if ($attrIds = $builder->pluck('model_id')->toArray()) { // model_id is user id

                    // should not more than 1 ...
                    if (count($attrIds) > 1) {
                        $this->error("Telegram id was assigned to more than one users.", [$attrIds, __METHOD__]);

                        return 0;
                    }

                    // @todo: check specific model exists?

                    return reset($attrIds);
                }
            }
        }

        return 0;
    }

    /**
     * Create or update a TelegramIdentity
     * Optionally also calls ensureUserByTelegramIdentity() to create a user
     *
     * @param  array  $telegramIdentityModelData
     * @param  array  $typeFilterForUserModel  in user models only add this types. for humans types use [null]
     *
     * @return array
     */
    public function ensureTelegramUser(
        array $telegramIdentityModelData,
        array $typeFilterForUserModel = [
            TelegramIdentity::TYPE_GROUP,
            TelegramIdentity::TYPE_CHANNEL,
        ]
    ): array {

        $result = [
            'User'             => null,
            'TelegramIdentity' => null,
        ];

        if ($telegramIdentityFound = $this->ensureTelegramIdentity($telegramIdentityModelData)) {

            // create or update a user represent telegram user, channel or group
            if (in_array($telegramIdentityFound->type, $typeFilterForUserModel)) {
                $user = $this->ensureUserByTelegramIdentity($telegramIdentityFound);
                $result['User'] = $user;
            }

            //
            $result['TelegramIdentity'] = $telegramIdentityFound;
        }

        return $result;
    }

    /**
     * Create or update a TelegramIdentity
     *
     * @param  array  $telegramIdentityModelData
     *
     * @return TelegramIdentity|null
     */
    public function ensureTelegramIdentity(array $telegramIdentityModelData): ?TelegramIdentity
    {
        if ($telegramUserId = data_get($telegramIdentityModelData, 'telegram_id')) {
            if ($telegramIdentityFound = TelegramIdentity::with([])->where('telegram_id', $telegramUserId)->first()) {
                $telegramIdentityFound->update($telegramIdentityModelData);
            } else {
                $telegramIdentityFound = TelegramIdentity::create([
                    'telegram_id' => $telegramUserId,
                    ... $telegramIdentityModelData,
                ]);
            }

            //
            return $telegramIdentityFound;
        }

        return null;
    }

    /**
     * @param  array  $typeFilterForUserModel
     *
     * @return void
     */
    public function ensureUsersByAllTelegramIdentities(
        array $typeFilterForUserModel = [
            TelegramIdentity::TYPE_GROUP,
            TelegramIdentity::TYPE_CHANNEL,
        ]
    ): void {
        $telegramEntities = TelegramIdentity::with([])->where('is_bot', 0)->whereIn('type', $typeFilterForUserModel)->get();
        if ($telegramEntities->isNotEmpty()) {
            $this->debug("Telegram entities relevant for user model: ".$telegramEntities->count().". Creating missing users.");
            foreach ($telegramEntities as $telegramEntity) {
                $this->ensureUserByTelegramIdentity($telegramEntity);
            }
        }
    }

    /**
     * Creates or update a user related to a TelegramIdentity
     *
     * @param  TelegramIdentity  $telegramEntity
     *
     * @return User|null
     */
    public function ensureUserByTelegramIdentity(TelegramIdentity $telegramEntity): ?User
    {
        if ($userId = $this->findUserByTelegramId($telegramEntity->telegram_id)) {

            return app(User::class)->with([])->whereId($userId)->first();

        } else {
            // create user assigned/related to this telegram id ...
            /** @var User $user */
            $user = app(User::class);
            /** @var User $user */
            $user = $user->makeWithDefaults([
                'name'     => app(UserService::class)->getNextAvailableUserName($telegramEntity->display_name),
                'email'    => 'fake_'.Str::orderedUuid().'@local.test',
                'password' => Str::random(30),
            ]);

            // force channel to telegram
            $this->setUserPreferredChannel($user, $telegramEntity);

            // save user
            $user->save();
            $this->info(sprintf("User for telegram entity created. ID: '%s', Name: '%s'", $user->getKey(), $user->name));

            $groupsAndChannels = [TelegramIdentity::TYPE_GROUP, TelegramIdentity::TYPE_CHANNEL];
            if (($telegramEntity->is_bot) || (in_array($telegramEntity->type, $groupsAndChannels))) {
                //  Call this AFTER saving (to get a valid user_id). Add acl group puppets/no humans ...
                if ($aclGroup = AclGroup::where('name', AclGroup::GROUP_NON_HUMANS)->first()) {
                    $user->aclGroups()->attach($aclGroup);
                }
            }
        }

        return $user;
    }

    /**
     * @param  User              $user
     * @param  TelegramIdentity  $telegramEntity
     *
     * @return void
     */
    public function setUserPreferredChannel(User $user, TelegramIdentity $telegramEntity): void
    {
        // add extra attribute: telegram_id
        $user->setExtraAttribute('telegram_id', $telegramEntity->telegram_id);
        $user->setExtraAttribute('use_telegram', true);
        $channels = $user->getPreferredNotificationChannels();
        if (!in_array(Telegram::name, $channels)) {
            $channels = Arr::prepend($channels, Telegram::name);
        }
        $user->setExtraAttribute(ExtraAttributeModel::ATTR_PREFERRED_NOTIFICATION_CHANNELS, $channels);
    }

}