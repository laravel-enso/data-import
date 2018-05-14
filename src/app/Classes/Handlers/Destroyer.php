<?php

namespace LaravelEnso\DataImport\app\Classes\Handlers;

use Illuminate\Database\Eloquent\Model;

class Destroyer extends Handler
{
    protected $model;

    public function __construct(Model $model)
    {
        parent::__construct();

        $this->model = $model;
    }

    public function run()
    {
        $this->fileManager
            ->delete($this->model->saved_name);
    }
}
