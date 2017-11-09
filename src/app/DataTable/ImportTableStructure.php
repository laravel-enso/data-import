<?php

namespace LaravelEnso\DataImport\app\DataTable;

use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataTable\app\Classes\TableStructure;

class ImportTableStructure extends TableStructure
{
    public function __construct()
    {
        $this->data = [
            'name'                => __('Past Imports'),
            'crtNo'               => __('#'),
            'actions'             => __('Actions'),
            'actionButtons'       => ['download', 'destroy'],
            'customActionButtons' => [
                ['icon' => 'fa fa-info-circle', 'class' => 'is-info', 'event' => 'get-summary'],
            ],
            'headerAlign'   => 'center',
            'bodyAlign'     => 'center',
            'appends'       => ['successful', 'errors'],
            'render'        => [2, 3],
            'notSearchable' => [2, 3],
            'notSortable'   => [2, 3],
            'enumMappings'  => [
                'type' => ImportTypes::class,
            ],
            'columns' => [
                0 => [
                    'label' => __('Import Type'),
                    'data'  => 'type',
                    'name'  => 'type',
                ],
                1 => [
                    'label' => __('File Name'),
                    'data'  => 'original_name',
                    'name'  => 'original_name',
                ],
                2 => [
                    'label' => __('Successful'),
                    'data'  => 'successful',
                    'name'  => 'successful',
                ],
                3 => [
                    'label' => __('Errors'),
                    'data'  => 'errors',
                    'name'  => 'errors',
                ],
                4 => [
                    'label' => __('Imported By'),
                    'data'  => 'created_by',
                    'name'  => 'users.last_name',
                ],
                5 => [
                    'label' => __('Date'),
                    'data'  => 'created_at',
                    'name'  => 'data_imports.created_at',
                ],
            ],
        ];
    }
}
