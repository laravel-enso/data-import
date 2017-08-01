<?php

namespace LaravelEnso\DataImport\app\DataTable;

use LaravelEnso\DataImport\app\Enums\ImportTypes;
use LaravelEnso\DataTable\app\Classes\TableStructure;

class ImportTableStructure extends TableStructure
{
    public function __construct()
    {
        $this->data = [
            'tableName'           => __('Past Imports'),
            'crtNo'               => __('#'),
            'actionButtons'       => __('Actions'),
            'customActionButtons' => [
                ['class' => 'btn-info fa fa-info-circle', 'event' => 'get-summary'],
            ],
            'headerAlign'         => 'center',
            'bodyAlign'           => 'center',
            'enumMappings'        => [
                'type' => ImportTypes::class,
            ],
            'columns'             => [
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
                    'label' => __('Date'),
                    'data'  => 'created_at',
                    'name'  => 'data_imports.created_at',
                ],
                3 => [
                    'label' => __('User'),
                    'data'  => 'created_by',
                    'name'  => 'users.last_name',
                ],
            ],
        ];
    }
}
