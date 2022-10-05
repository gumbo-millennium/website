<?php

declare(strict_types=1);

namespace Tests\Feature\Excel\Imports;

use App\Excel\Imports\ActivityImport;
use App\Models\Activity;
use App\Models\Role;
use finfo;
use InvalidArgumentException;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ActivityImportTest extends TestCase
{
    /**
     * Ensure the created import "export" is proper.
     */
    public function test_export_creation(): void
    {
        $result = Excel::raw(new ActivityImport(), ExcelExcel::ODS);

        $resultMime = (new finfo(FILEINFO_MIME_TYPE))->buffer($result);

        $this->assertStringStartsWith('application/', $resultMime);
    }

    /**
     * Check the details for each column on one import.
     */
    public function test_single_import_validity(): void
    {
        Excel::import(new ActivityImport(), test_path('Fixtures/resources/excel/valid-single.ods'));

        $activity = Activity::findBySlug('new-years-eve-2022');

        $this->assertNotNull($activity);

        $this->assertEquals('New Year\'s Eve', $activity->name);
        $this->assertEquals('New Year, New Me!', $activity->tagline);
        $this->assertEquals('Grote Markt Zwolle', $activity->location);
        $this->assertEquals('Grote Markt, Zwolle', $activity->location_address);

        // Check dates
        $this->assertEquals('31-12-2022 22:00', $activity->start_date->format('d-m-Y H:i'));
        $this->assertEquals('01-01-2023 04:00', $activity->end_date->format('d-m-Y H:i'));

        // Check if publish date is derrived from start date
        $this->assertEquals(
            $activity->start_date->subMonth()->startOfDay(),
            $activity->published_at,
        );
    }

    /**
     * Check if the role is assigned correctly, if provided.
     */
    public function test_proper_role_assignment(): void
    {
        $role = Role::forceCreate([
            'name' => 'testing',
            'title' => 'Test Role',
            'guard_name' => 'web',
        ]);

        Excel::import(new ActivityImport($role), test_path('Fixtures/resources/excel/valid-sheet.ods'));

        $allActivities = Activity::with('role')->get();

        $this->assertCount(3, $allActivities);

        foreach ($allActivities as $activity) {
            $this->assertTrue($role->is($activity->role));
        }
    }

    /**
     * Test a valid import, should be fine.
     */
    public function test_importing_valid_sheet(): void
    {
        Excel::import(new ActivityImport(), test_path('Fixtures/resources/excel/valid-sheet.ods'));

        $this->assertDatabaseHas('activities', [
            'slug' => 'my-little-party-2022',
        ]);
        $this->assertDatabaseHas('activities', [
            'slug' => 'pirate-party-2022',
        ]);
        $this->assertDatabaseHas('activities', [
            'slug' => 'new-years-eve-2022',
        ]);
    }

    /**
     * Check to make sure a sheet with empty rows is imported without errors.
     */
    public function test_importing_gapped_sheet_works(): void
    {
        Excel::import(new ActivityImport(), test_path('Fixtures/resources/excel/valid-gapped.ods'));

        $this->assertDatabaseHas('activities', [
            'slug' => 'my-little-party-2022',
        ]);
        $this->assertDatabaseHas('activities', [
            'slug' => 'pirate-party-2022',
        ]);
    }

    /**
     * @dataProvider invalidSheetProvider
     */
    public function test_invalid_cases(string $file, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        Excel::import(new ActivityImport(), test_path("Fixtures/resources/excel/{$file}.ods"));
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function invalidSheetProvider(): array
    {
        return [
            'end date is before start date' => [
                'invalid-field-end-before-start',
                'Einddatum moet een datum na startdatum zijn.',
            ],
            'location is too long' => [
                'invalid-field-location-too-long',
                'Locatie moet tussen 4 en 64 karakters zijn.',
            ],
            'end date is missing' => [
                'invalid-field-missing-end',
                'Einddatum is verplicht.',
            ],
            'location is missing' => [
                'invalid-field-missing-location',
                'Locatie is verplicht.',
            ],
            'missing required columns' => [
                'invalid-columns',
                'Startdatum is verplicht.',
            ],
        ];
    }
}
