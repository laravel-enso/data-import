<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataImport\Enums\Statuses;
use LaravelEnso\DataImport\Exceptions\Import as ImportException;
use LaravelEnso\DataImport\Jobs\Import as ImportJob;
use LaravelEnso\DataImport\Models\Import;
use LaravelEnso\DataImport\Models\RejectedImport;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Files\Models\Type;
use LaravelEnso\Users\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportManagementTest extends TestCase
{
    use RefreshDatabase;

    private const ImportType = 'userGroups';
    private const Template = __DIR__.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'userGroups.json';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed()
            ->actingAs(User::first());

        Config::set(['enso.imports.configs.userGroups' => [
            'label'    => 'User Groups',
            'template' => Str::of(self::Template)->replace(base_path(), ''),
        ]]);
    }

    protected function tearDown(): void
    {
        File::query()->get()
            ->each(fn (File $file) => Storage::delete($file->path()));

        parent::tearDown();
    }

    #[Test]
    public function can_cancel_a_running_import(): void
    {
        $import = $this->import([
            'status' => Statuses::Processing,
        ]);

        $import->cancel();

        $this->assertSame(Statuses::Cancelled, $import->fresh()->status);
        $this->assertNull($import->fresh()->batch);
    }

    #[Test]
    public function cannot_cancel_a_non_running_import(): void
    {
        $import = $this->import([
            'status' => Statuses::Finalized,
        ]);

        $this->expectException(ImportException::class);

        $import->cancel();
    }

    #[Test]
    public function cancel_endpoint_cancels_a_running_import(): void
    {
        $import = $this->import([
            'status' => Statuses::Waiting,
        ]);

        $this->patch(route('import.cancel', $import, false))
            ->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('The import was cancelled successfully'),
            ]);

        $this->assertSame(Statuses::Cancelled, $import->fresh()->status);
    }

    #[Test]
    public function restart_resets_counters_deletes_rejected_and_dispatches_import(): void
    {
        Bus::fake();

        $import = $this->import([
            'successful' => 5,
            'failed'     => 2,
            'status'     => Statuses::Cancelled,
        ]);

        $rejected = RejectedImport::create([
            'import_id' => $import->id,
        ]);

        $import->restart();

        $this->assertSame(0, $import->fresh()->successful);
        $this->assertSame(0, $import->fresh()->failed);
        $this->assertSame(Statuses::Waiting, $import->fresh()->status);
        $this->assertNull($rejected->fresh());
        Bus::assertDispatched(ImportJob::class);
    }

    #[Test]
    public function restart_endpoint_restarts_the_import(): void
    {
        Bus::fake();

        $import = $this->import([
            'successful' => 3,
            'failed'     => 1,
            'status'     => Statuses::Cancelled,
        ]);

        $this->patch(route('import.restart', $import, false))
            ->assertStatus(200)
            ->assertJsonFragment([
                'message' => __('The import was restarted'),
            ]);

        $this->assertSame(Statuses::Waiting, $import->fresh()->status);
        Bus::assertDispatched(ImportJob::class);
    }

    #[Test]
    public function force_delete_removes_a_non_deletable_import(): void
    {
        $import = $this->import([
            'status' => Statuses::Processing,
        ]);

        $import->forceDelete();

        $this->assertNull($import->fresh());
    }

    #[Test]
    public function purge_cancels_expired_running_imports_and_purges_files_of_expired_deletable_imports(): void
    {
        Config::set('enso.imports.retainFor', 1);

        $expiredRunning = $this->import([
            'status'     => Statuses::Processing,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $expiredFinalized = $this->import([
            'status'     => Statuses::Finalized,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $file = $this->attachFileTo($expiredFinalized, 'import-purge.xlsx');
        $expiredFinalized->file()->associate($file)->save();

        $freshImport = $this->import([
            'status'     => Statuses::Finalized,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('enso:data-import:purge')->assertExitCode(0);

        $this->assertSame(Statuses::Cancelled, $expiredRunning->fresh()->status);
        $this->assertNull($expiredFinalized->fresh()->file_id);
        $this->assertNotNull($freshImport->fresh());
        Storage::assertMissing($file->path());
    }

    #[Test]
    public function cancel_stuck_command_cancels_old_running_imports(): void
    {
        Config::set('enso.imports.cancelStuckAfter', 1);

        $stuckImport = $this->import([
            'status'     => Statuses::Processing,
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        $freshImport = $this->import([
            'status'     => Statuses::Processing,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('enso:data-import:cancel-stuck')->assertExitCode(0);

        $this->assertSame(Statuses::Cancelled, $stuckImport->fresh()->status);
        $this->assertNull($stuckImport->fresh()->batch);
        $this->assertSame(Statuses::Processing, $freshImport->fresh()->status);
    }

    #[Test]
    public function template_endpoint_returns_the_generated_template(): void
    {
        $this->get(route('import.template', self::ImportType, false))
            ->assertStatus(200);
    }

    #[Test]
    public function validates_missing_file_and_invalid_type_when_importing(): void
    {
        $this->post(route('import.store', [], false), [
            'type' => 'invalid-type',
        ])->assertInvalid(['import', 'type']);
    }

    private function import(array $attributes = []): Import
    {
        return Import::factory()->create($attributes + [
            'type' => self::ImportType,
        ]);
    }

    private function attachFileTo(Import $import, string $filename): File
    {
        $savedName = "saved-{$filename}";
        $path = Type::for(Import::class)->path($savedName);

        Storage::put($path, 'import');

        return File::attach($import, $savedName, $filename);
    }
}
