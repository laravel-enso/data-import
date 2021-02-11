<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Exceptions\Template as Exception;
use LaravelEnso\DataImport\Services\Validators\Params;
use LaravelEnso\DataImport\Services\Validators\Template as Validator;
use LaravelEnso\DataImport\Services\Validators\Validator as CustomValidator;
use LaravelEnso\Helpers\Services\JsonReader;
use LaravelEnso\Helpers\Services\Obj;

class Template
{
    private Obj $template;
    private array $columnRules;
    private array $paramRules;
    private array $chunkSizes;

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

    public function queue(): string
    {
        return $this->template->has('queue')
            ? $this->template->get('queue')
            : Config::get('enso.imports.queues.processing');
    }

    public function header(string $sheet): Collection
    {
        return $this->columns($sheet)->pluck('name');
    }

    public function descriptions(string $sheet): Collection
    {
        return $this->columns($sheet)->pluck('description');
    }

    public function columnRules(string $sheet): array
    {
        return $this->columnRules ??= $this->columns($sheet)
            ->filter(fn ($column) => $column->has('validations'))
            ->mapWithKeys(fn ($column) => [
                $column->get('name') => $column->get('validations'),
            ])->toArray();
    }

    public function paramRules(): array
    {
        return $this->paramRules ??= $this->params()
            ->filter(fn ($param) => $param->has('validations'))
            ->mapWithKeys(fn ($param) => [
                $param->get('name') => $param->get('validations'),
            ])->toArray();
    }

    public function chunkSize(string $sheet): int
    {
        return $this->chunkSizes[$sheet]
            ??= $this->sheet($sheet)->has('chunkSize')
            ? $this->sheet($sheet)->get('chunkSize')
            : (int) Config::get('enso.imports.chunkSize');
    }

    public function importer(string $sheet): Importable
    {
        $class = $this->sheet($sheet)->get('importerClass');

        return new $class();
    }

    public function customValidator(string $sheet): ?CustomValidator
    {
        if ($this->sheet($sheet)->has('validatorClass')) {
            $class = $this->sheet($sheet)->get('validatorClass');

            return new $class();
        }

        return null;
    }

    public function params(bool $validations = true): Obj
    {
        return (new Obj($this->template->get('params', [])))
            ->when(! $validations, fn ($params) => $params
                ->map->except('validations'));
    }

    public function sheets(): Obj
    {
        return $this->template->get('sheets');
    }

    public function nextSheet(string $name): ?Obj
    {
        $index = $this->sheets()->search(fn ($sheet) => $sheet->get('name') === $name);

        return $this->sheets()->get($index + 1);
    }

    private function columns(string $sheet): Obj
    {
        return $this->sheet($sheet)->get('columns');
    }

    private function sheet(string $name): Obj
    {
        return $this->sheets()
            ->first(fn ($sheet) => $sheet->get('name') === $name);
    }

    private function validate(): void
    {
        (new Validator($this->template))->run();
        (new Params($this->template))->run();
    }

    private function shouldValidate(): bool
    {
        return in_array(
            Config::get('enso.imports.validations'),
            [App::environment(), 'always']
        );
    }

    private function template(string $type): Obj
    {
        $template = Config::get("enso.imports.configs.{$type}.template");

        if (! $template) {
            throw Exception::disabled();
        }

        return (new JsonReader(base_path($template)))->obj();
    }
}
