<?php

namespace LaravelEnso\DataImport\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataImport\Contracts\Importable;
use LaravelEnso\DataImport\Exceptions\Template as Exception;
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

    public function header(string $sheetName): Collection
    {
        return $this->columns($sheetName)->pluck('name');
    }

    public function descriptions(string $sheetName): Collection
    {
        return $this->columns($sheetName)->pluck('description');
    }

    public function columnRules(string $sheetName): array
    {
        return $this->columnRules ??= $this->columns($sheetName)
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

    public function chunkSize($sheetName): int
    {
        return $this->chunkSizes[$sheetName]
            ??= $this->sheet($sheetName)->has('chunkSize')
            ? $this->sheet($sheetName)->get('chunkSize')
            : (int) Config::get('enso.imports.chunkSize');
    }

    public function importer($sheetName): Importable
    {
        $class = $this->sheet($sheetName)->get('importerClass');

        return new $class();
    }

    public function customValidator($sheetName): ?CustomValidator
    {
        if ($this->sheet($sheetName)->has('validatorClass')) {
            $class = $this->sheet($sheetName)->get('validatorClass');

            return new $class();
        }

        return null;
    }

    public function params(): Obj
    {
        return new Obj($this->template->get('params', []));
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

    private function columns(string $sheetName): Obj
    {
        return $this->sheet($sheetName)->get('columns');
    }

    private function sheet(string $sheetName): Obj
    {
        return $this->sheets()
            ->first(fn ($sheet) => $sheet->get('name') === $sheetName);
    }

    private function validate(): void
    {
        (new Validator($this->template))->run();
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
