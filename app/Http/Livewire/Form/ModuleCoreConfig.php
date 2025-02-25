<?php

namespace Modules\TelegramApi\app\Http\Livewire\Form;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\SystemBase\app\Http\Livewire\Form\Base\ModuleCoreConfigBase;
use Modules\TelegramApi\app\Models\TelegramIdentity as TelegramIdentityModel;

class ModuleCoreConfig extends ModuleCoreConfigBase
{
    public function extendDataSource(JsonResource $dataSource): void
    {
        $dataSource->resource['telegram_identities'] = TelegramIdentityModel::with([]); // just all
    }

    /**
     * @return array[]
     */
    public function getTabPages(): array
    {
        return [
            [
                'tab'     => [
                    'label' => __('Telegram Identities'),
                ],
                'content' => [
                    'form_elements' => [
                        'telegram_identities' => [
                            'html_element' => 'element-dt-selected-with-form',
                            'label'        => __('Telegram Identities'),
                            'css_group'    => 'col-12',
                            'options'      => [
                                'form'          => 'telegram-api::form.telegram-identity',
                                'form_options'  => [],
                                'table'         => 'telegram-api::data-table.telegram-identity',
                                'table_options' => [
                                    'hasCommands' => true,
                                    'editable'    => true,
                                    'canAddRow'   => true,
                                ],
                            ],
                            'validator'    => [
                                'nullable',
                                'array',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'tab'      => [
                    'label' => __('Telegram Users'),
                ],
                'content'  => [
                    'form_elements' => [
                        'telegram_users' => [
                            'html_element' => 'element-dt-split-default',
                            'label'        => __('Users'),
                            'css_group'    => 'col-12',
                            'options'      => [
                                'table'         => 'website-base::data-table.user',
                                'table_options' => [
                                    'hasCommands' => false,
                                    'editable'    => false,
                                    'canAddRow'   => false,
                                ],
                            ],
                            'validator'    => [
                                'nullable',
                                'array',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}