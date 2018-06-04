<?php

namespace LaravelEnso\DataImport\app\Classes\Handlers;

use Illuminate\Database\Eloquent\Model;

class Presenter extends Handler
{
    private $model;

    public function __construct(Model $model)
    {
        parent::__construct();

        $this->model = $model;
    }

    public function download()
    {
        return $this->fileManager
            ->download(
                $this->model->original_name,
                $this->model->saved_name
            );
    }
}
