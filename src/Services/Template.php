<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Exceptions\Template as Exception;
use LaravelEnso\DataImport\Services\Validators\Params;
use LaravelEnso\DataImport\Services\Validators\Template as Validator;
use LaravelEnso\Helpers\Services\JsonReader;
use LaravelEnso\Helpers\Services\Obj;

abstract class Template
{
    protected Obj $template;
    protected array $columnRules;
    protected array $paramRules;
    protected array $chunkSizes;

    public function __construct(string $type)
    {
        $this->template = $this->template($type);
        $this->chunkSizes = [];

        if ($this->shouldValidate()) {
            $this->validate();
        }
    }

    public function timeout(): int
    {
        return $this->template->has('timeout')
            ? $this->template->get('timeout')
            : (int) Config::get('enso.imports.timeout');
    }

    public function notifies(): bool
    {
        return $this->template->has('notifies')
            && $this->template->get('notifies');
    }

    public function queue(): string
    {
        return $this->template->has('queue')
            ? $this->template->get('queue')
            : Config::get('enso.imports.queues.processing');
    }

    public function paramRules(): array
    {
        return $this->paramRules ??= $this->params()
            ->filter(fn ($param) => $param->has('validations'))
            ->mapWithKeys(fn ($param) => [
                $param->get('name') => $param->get('validations'),
            ])->toArray();
    }

    public function params(bool $validations = true): Obj
    {
        return (new Obj($this->template->get('params', [])))
            ->when(! $validations, fn ($params) => $params
                ->map->except('validations'))
            ->each(fn ($param) => $this->optionallySetOptions($param));
    }

    protected function validate(): void
    {
        (new Validator($this->template))->run();
        (new Params($this->template))->run();
    }

    protected function shouldValidate(): bool
    {
        return in_array(
            Config::get('enso.imports.validations'),
            [App::environment(), 'always']
        );
    }

    protected function template(string $type): Obj
    {
        $template = Config::get("enso.imports.configs.{$type}.template");

        if (! $template) {
            throw Exception::disabled();
        }

        return (new JsonReader(base_path($template)))->obj();
    }

    protected function optionallySetOptions($param)
    {
        $options = $param->get('options');

        if ($options && class_exists($options)) {
            $param->put('options', $options::select());
        }
    }
}
