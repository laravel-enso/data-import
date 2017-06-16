<?php

namespace LaravelEnso\DataImport\App\Http\Controllers;

use App\Http\Controllers\Controller;
use LaravelEnso\DataImport\app\Classes\Import;
use LaravelEnso\DataImport\app\DataTable\DataImportTableStructure;
use LaravelEnso\DataImport\app\Enums\DataImportTypesEnum;
use LaravelEnso\DataImport\app\Models\DataImport;
use LaravelEnso\DataTable\app\Traits\DataTable;
use LaravelEnso\FileManager\Classes\FileManager;
use LaravelEnso\Select\app\Traits\SelectListBuilder;

class DataImportController extends Controller
{
    use DataTable, SelectListBuilder;

    protected $tableStructureClass = DataImportTableStructure::class;
    private $fileManager;

    public function __construct()
    {
        $this->fileManager = new FileManager(config('laravel-enso.paths.imports'), config('laravel-enso.paths.temp'));
    }

    public static function getTableQuery()
    {
        $query = DataImport::select(\DB::raw('
                data_imports.id as DT_RowId,
                data_imports.type,
                data_imports.original_name,
                data_imports.comment,
                data_imports.created_at,
                concat(users.first_name, " ", users.last_name) as created_by
            '))->join('users', 'data_imports.created_by', '=', 'users.id');

        return $query;
    }

    public function index()
    {
        $importTypes = $this->buildSelectList((new DataImportTypesEnum())->getData());

        return view('laravel-enso/data-import::dataImport.index', compact('importTypes'));
    }

    /** Load the summary for a given import
     * @param DataImport $dataImport
     *
     * @return array
     */
    public function getSummary(DataImport $dataImport)
    {
        return [
            'summary' => $dataImport->summary,
        ];
    }

    /** Processes an import and gives back a summary
     * @return array
     */
    public function run()
    {
        $this->checkIfFileIsValid();
        $import = new Import(request('type'), request('file_0'));

        \DB::transaction(function () use ($import) {
            $import->run();

            if ($import->isValid()) {
                $this->fileManager->startSingleFileUpload(request('file_0'));
                $dataImport = new DataImport($this->fileManager->uploadedFiles->first());
                $dataImport->type = request('type');
                $dataImport->comment = request('comment');
                $dataImport->summary = $import->getSummary();
                $dataImport->save();
                $this->fileManager->commitUpload();
            }
        });

        return ['summary' => $import->getSummary()];
    }

    private function checkIfFileIsValid()
    {
        if (!request('file_0')->isValid()) {
            throw new \EnsoException('The file is not valid', 'error', []);
        }
    }

    /** Downloads a file corresponding to a successful import
     * @param $dataImport
     *
     * @return mixed
     */
    public function download(DataImport $dataImport)
    {
        $fileWrapper = $this->fileManager->getFile($dataImport->saved_name);
        $fileWrapper->originalName = $dataImport->original_name;

        return $fileWrapper->getDownloadResponse();
    }

    /** Delete an import line as well as the corresponding file
     * @param DataImport $dataImport
     *
     * @return \LaravelEnso\FileManager\Classes\FileManagerStatus
     */
    public function destroy(DataImport $dataImport)
    {
        \DB::transaction(function () use ($dataImport) {
            $dataImport->delete();
            $this->fileManager->delete($dataImport->saved_name);
        });

        return $this->fileManager->getStatus();
    }
}
