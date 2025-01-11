<?php

namespace Modules\TelegramApi\app\Http\Controllers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\SystemBase\app\Models\JsonViewResponse;
use Modules\TelegramApi\app\Services\TelegramService;

class TelegramApiController extends Controller
{
    /**
     * @var TelegramService
     */
    protected TelegramService $telegramService;

    /**
     * @param  TelegramService  $telegramService
     */
    public function __construct(TelegramService $telegramService)
    {
        $this->telegramService = $telegramService;
    }

    /**
     * @param  Request  $request
     *
     * @return Application|ResponseFactory|Response
     */
    public function login(Request $request): Response|Application|ResponseFactory
    {
        /** @var JsonViewResponse $json */
        $json = app(JsonViewResponse::class);

        if (!($requestUser = $request->post('user'))) {
            $json->setErrorMessage('Invalid request data.');

            return $json->go();
        }

        $telegramIdentityModelData = [
            'telegram_id'  => data_get($requestUser, 'id'),
            'display_name' => data_get($requestUser, 'first_name'),
            'username'     => data_get($requestUser, 'username'),
            // 'img' => data_get($requestUser, 'photo_url'),
        ];

        // Create or update Telegram Identity and User related to this identity ...
        $u = $this->telegramService->ensureTelegramUser($telegramIdentityModelData, [null]);
        if (($user = $u['User']) && $u['TelegramIdentity']) {

            // @todo: loading screen ...

            // Need to reload, otherwise it won't work!
            $user = app(\App\Models\User::class)->with([])->find($user->id);
            event(new Registered($user));
            Auth::login($user);

            // @todo: add welcome message

            // $this->addSuccessMessage(__('maybe_email_sent'));

        } else {
            $json->setErrorMessage('Benutzer konnte leider nicht angelegt werden.');
        }

        return $json->go();
    }

}
