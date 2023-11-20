<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Avg;

use App\Jobs\Avg\CreateUserDataExport;
use App\Models\DataExport;
use App\Models\User;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CreateUserDataExportTest extends TestCase
{
    private User $user;

    private DataExport $export;

    /**
     * @before
     */
    public function createModelsAtStart(): void
    {
        $this->afterApplicationCreated(function () {
            $this->user = User::factory()->create();
            $this->export = DataExport::factory()->create([
                'user_id' => $this->user->id,
            ]);
        });
    }

    public function test_basic_handling(): void
    {
        CreateUserDataExport::dispatchSync($this->export);

        $export = $this->export->refresh();

        // Check if set
        $this->assertNotNull($export->completed_at);
        $this->assertNotNull($export->path);

        // Check if written
        Storage::assertExists($export->path);

        // Get a stream to copy
        $readStream = Storage::readStream($export->path);

        // Get a target
        $target = tempnam(sys_get_temp_dir(), 'export');
        $writeStream = fopen($target, 'w');
        if (! $writeStream) {
            @fclose($readStream);
            $this->fail('Failed to check file contents');
        }

        // Copy from Storage to local FS
        stream_copy_to_stream($readStream, $writeStream);
        fclose($readStream);
        fclose($writeStream);

        // Check the file contents
        $zip = new ZipArchive();
        $zip->open($target);

        try {
            $this->assertNotNull($zip->getFromName('data.json'));
        } finally {
            $zip->close();
            @unlink($target);
        }
    }

    public function test_handling_of_completed_jobs(): void
    {
        Date::setTestNow('2021-01-01T00:00:00');

        $this->export = DataExport::factory()->withData()->create([
            'user_id' => $this->user->id,
            'completed_at' => Date::now()->subWeek(),
        ]);

        $currentPath = $this->export->path;
        $currentCompletionDate = $this->export->completed_at;

        CreateUserDataExport::dispatchSync($this->export);

        $export = $this->export->refresh();

        $this->assertSame($currentPath, $export->path);
        $this->assertSame(
            $currentCompletionDate->toIso8601String(),
            $export->completed_at->toIso8601String(),
        );
    }
}
