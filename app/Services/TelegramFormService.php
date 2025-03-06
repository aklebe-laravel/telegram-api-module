<?php

namespace Modules\TelegramApi\app\Services;

use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\SystemBase\app\Services\CacheService;
use Modules\SystemBase\app\Services\SystemService;

class TelegramFormService extends BaseService
{
    /**
     * @return array
     */
    public static function getFormElementTelegramGroupOptions(): array
    {
        return app(CacheService::class)->rememberFrontend('form_element.select_telegram_group.options', function () {
            /** @var SystemService $systemService */
            $systemService = app('system_base');


            /** @var TelegramApiService $telegramApiService */
            $telegramApiService = app(TelegramApiService::class);
            if ($telegramGroups = $telegramApiService->getBotGroups()) {
                $telegramGroups = $telegramGroups->toArray();
            } else {
                $telegramGroups = [];
            }

            return $systemService->toHtmlSelectOptions($telegramGroups, ['display_name'], 'id', $systemService->selectOptionsSimple[$systemService::selectValueNoChoice]);
        });
    }

    /**
     * @param  array  $mergeData
     *
     * @return array
     */
    public static function getFormElementTelegramGroup(array $mergeData = []): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct([
            'html_element' => 'select',
            'options'      => static::getFormElementTelegramGroupOptions(),
            'label'        => __('Telegram Group'),
            'description'  => __('Telegram Group'),
            'validator'    => [
                'nullable',
                'integer',
            ],
            'css_group'    => 'col-12 col-md-6',
        ], $mergeData);
    }


}