<?php

namespace Modules\TelegramApi\app\Forms;

use Modules\Form\app\Forms\Base\ModelBase;
use Modules\TelegramApi\app\Models\TelegramIdentity as TelegramIdentityModel;

class TelegramIdentity extends ModelBase
{
    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    protected array $objectRelations = ['children', 'finders'];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Telegram Identity';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Telegram Identities';

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        return [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->getDataSource(), 'id'),
            'description'  => __('form_telegram_identity_description'),
            'tab_controls' => [
                'base_item' => [
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'id'              => [
                                        'html_element' => 'hidden',
                                        'label'        => __('ID'),
                                        'validator'    => ['nullable', 'integer'],
                                    ],
                                    'telegram_id'     => [
                                        'html_element' => 'hidden',
                                        'validator'    => ['required', 'integer'],
                                    ],
                                    'is_enabled'      => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enabled or disabled for whole interacting.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-md-6 col-lg-3',
                                    ],
                                    'is_bot'          => [
                                        'html_element' => 'switch',
                                        'disabled'     => true,
                                        'label'        => __('Is Bot'),
                                        'description'  => __('Determines whether the telegram identity is a bot.'),
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-md-6 col-lg-3',
                                    ],
                                    'type'            => [
                                        'html_element' => 'select',
                                        'disabled'     => true,
                                        'options'      => [...app('system_base')->getHtmlSelectOptionNoValue('No choice'), ... TelegramIdentityModel::Types],
                                        'label'        => __('Type'),
                                        'css_group'    => 'col-12 col-md-6 col-lg-3',
                                    ],
                                    'language_code'   => [
                                        'html_element' => 'website-base::select_country',
                                        //'disabled'     => true,
                                        'label'        => __('Language'),
                                        'cmpCi'        => true,
                                        'description'  => __('Language'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:10',
                                        ],
                                        'css_group'    => 'col-12 col-md-6 col-lg-3',
                                    ],
                                    'display_name'    => [
                                        'html_element' => 'text',
                                        'disabled'     => true,
                                        'label'        => __('Display Name'),
                                        'validator'    => ['string', 'Max:100'],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'username'        => [
                                        'html_element' => 'text',
                                        'disabled'     => true,
                                        'label'        => __('Username'),
                                        'validator'    => ['string', 'Max:100'],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'position'        => [
                                        'html_element' => 'number_int',
                                        'label'        => __('Position'),
                                        'description'  => __('Position'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'additional_data' => [
                                        'html_element' => 'object_to_json',
                                        'description'  => __('Formatted json. See README.md for details'),
                                        'label'        => __('Additional Data'),
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:50000',
                                        ],
                                        'css_group'    => 'col-12',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'tab'     => [
                                'label' => __('Children'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'children' => [
                                        'html_element' => 'element-dt-split-default',
                                        'label'        => __('Children'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'telegram-api::form.telegram-identity',
                                            'form_options'  => [],
                                            'table'         => 'telegram-api::data-table.telegram-identity',
                                            'table_options' => [
                                                'hasCommands' => false,
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
                            'tab'     => [
                                'label' => __('Finders'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'finders' => [
                                        'html_element' => 'element-dt-split-default',
                                        'label'        => __('Finders'),
                                        'css_group'    => 'col-12',
                                        'options'      => [
                                            'form'          => 'telegram-api::form.telegram-identity',
                                            'form_options'  => [],
                                            'table'         => 'telegram-api::data-table.telegram-identity',
                                            'table_options' => [
                                                'hasCommands' => false,
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
                    ],
                ],
            ],
        ];
    }

}