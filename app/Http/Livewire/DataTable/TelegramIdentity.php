<?php

namespace Modules\TelegramApi\app\Http\Livewire\DataTable;

use Modules\Acl\app\Models\AclResource;
use Modules\DataTable\app\Http\Livewire\DataTable\Base\BaseDataTable;
use Modules\WebsiteBase\app\Http\Livewire\DataTable\BaseWebsiteBaseDataTable;

class TelegramIdentity extends BaseDataTable
{
    use BaseWebsiteBaseDataTable;

    /**
     * Minimum restrictions to allow this component.
     */
    public const array aclResources = [AclResource::RES_DEVELOPER, AclResource::RES_TRADER];

    /**
     * @return array[]
     */
    public function getColumns(): array
    {
        return [
            [
                'name'       => 'id',
                'label'      => 'ID',
                'format'     => 'number',
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-lg text-muted font-monospace text-end w-5',
            ],
            [
                'name'     => 'is_enabled',
                'label'    => __('Enabled'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-red-green',
                'css_all'  => 'hide-mobile-show-lg text-center w-5',
                'sortable' => true,
                'icon'     => 'check',
            ],
            [
                'name'     => 'is_bot',
                'label'    => __('Is Bot'),
                'view'     => 'data-table::livewire.js-dt.tables.columns.bool-yes-no',
                'css_all'  => 'hide-mobile-show-lg text-center w-5',
                'sortable' => true,
                'icon'     => 'check',
            ],
            [
                'name'       => 'display_name',
                'label'      => __('Display Name'),
                'searchable' => true,
                'sortable'   => true,
                'options'    => [
                    'has_open_link' => $this->canEdit(),
                    'str_limit'     => 30,
                ],
                'css_all'    => 'hide-mobile-show-lg w-20',
            ],
            [
                'name'       => 'username',
                'label'      => __('Username'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'w-20',
            ],
            [
                'name'       => 'type',
                'label'      => __('Type'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-md w-20',
            ],
            [
                'name'       => 'position',
                'label'      => __('Position'),
                'searchable' => true,
                'sortable'   => true,
                'css_all'    => 'hide-mobile-show-md w-5',
            ],
            [
                'name'       => 'updated_at',
                'label'      => 'Updated',
                'searchable' => true,
                'sortable'   => true,
                'view'       => 'data-table::livewire.js-dt.tables.columns.datetime-since',
                'css_all'    => 'hide-mobile-show-lg w-5',
            ],
        ];
    }


}
