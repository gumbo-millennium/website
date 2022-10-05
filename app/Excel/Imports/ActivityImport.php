<?php

declare(strict_types=1);

namespace App\Excel\Imports;

use App\Models\Activity;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Str;

class ActivityImport implements FromCollection, ShouldAutoSize, ToModel, WithHeadingRow, WithHeadings, WithProperties, WithStyles
{
    public const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public const COLUMNS = [
        'name' => [
            'name' => 'Name',
            'default' => 'My Activity',
            'rules' => [
                'required',
                'between:4,255',
            ],
        ],
        'tagline' => [
            'name' => 'Tagline',
            'default' => 'Have fun on my party!',
            'rules' => [
                'required',
                'between:4,255',
            ],
        ],
        'location' => [
            'name' => 'Location',
            'default' => 'My House',
            'rules' => [
                'required',
                'between:2,64',
            ],
        ],
        'location_address' => [
            'name' => 'Location Address',
            'default' => 'My Street 1',
            'rules' => [
                'max:190',
            ],
        ],
        'start_date' => [
            'name' => 'Start Date',
            'default' => '2022-01-01',
            'format' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ],
        'start_time' => [
            'name' => 'Start Time',
            'default' => '20:00',
            'format' => NumberFormat::FORMAT_DATE_TIME3,
        ],
        'end_date' => [
            'name' => 'End Date',
            'default' => '2022-01-01',
            'format' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ],
        'end_time' => [
            'name' => 'End Time',
            'default' => '23:00',
            'format' => NumberFormat::FORMAT_DATE_TIME3,
        ],
    ];

    public const VALIDATION_RULES = [
        'name' => [
            'required',
            'between:4,255', ],
        'tagline' => [
            'required',
            'between:4,255',
        ],
        'location' => [
            'required',
            'between:4,64',
        ],
        'location_address' => [
            'max:190',
        ],
        'start_date' => [
            'required',
            'date',
        ],
        'end_date' => [
            'required',
            'date',
            'after:start_date',
        ],
    ];

    public function __construct(private ?Role $activityRole = null)
    {
        // Intentionally left empty
    }

    public function collection(): Collection
    {
        return new Collection([Arr::pluck(self::COLUMNS, 'default')]);
    }

    public function headings(): array
    {
        return array_map(fn ($column) => __($column['name']), self::COLUMNS);
    }

    public function columnFormats(): array
    {
        $formats = array_map(fn ($column) => $column['format'] ?? null, self::COLUMNS);

        return Collection::make(Str::of(self::ALPHABET)->substr(0, count($formats))->split(''))
            ->combine($formats)
            ->filter()
            ->all();
    }

    public function properties(): array
    {
        return [
            'creator' => 'Gumbo Millennium',
            'title' => 'Import Format for Activities',
            'description' => 'Bulk-import activities using this template.',
            'subject' => 'Activities',
            'category' => 'Templates',
            'company' => 'Gumbo Millennium',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Freeze the header
        $sheet->freezePane('A2');

        // Format the header
        $cellRange = sprintf('A1:%s1', self::ALPHABET[count(self::COLUMNS) - 1]);
        $cell = $sheet->getStyle($cellRange);
        $cell->setFont($cell->getFont()->setBold(true));
        $cell->getProtection()->setLocked(Protection::PROTECTION_PROTECTED);
    }

    public function model(array $row)
    {
        // Skip if headings table
        if (Arr::first($row) === Arr::first($this->headings())) {
            return null;
        }

        // Skip if empty
        if (implode('', $row) === '') {
            return null;
        }

        // Map values using their snake-cased column name
        $mappedValues = Collection::make(self::COLUMNS)
            ->keys()
            ->combine($this->headings())
            ->map(fn ($heading) => Arr::get($row, Str::snake($heading)))
            ->map(fn ($value) => is_float($value) ? ExcelDate::excelToDateTimeObject($value) : $value);

        // Compute date, if present
        $activityStart = $mappedValues->get('start_date') && $mappedValues->get('start_time')
            ? Date::parse($mappedValues->get('start_date'))->setTimeFrom($mappedValues->get('start_time'))->setTimezone('Europe/Amsterdam')
            : null;

        $activityEnd = $mappedValues->get('end_date') && $mappedValues->get('end_time')
            ? Date::parse($mappedValues->get('end_date'))->setTimeFrom($mappedValues->get('end_time'))->setTimezone('Europe/Amsterdam')
            : null;

        $activityYear = $activityStart?->year;
        $publishAt = $activityStart?->clone()->subMonth()->startOfDay();

        // Build object
        $proposedData = [
            'name' => $mappedValues->get('name'),
            'slug' => Str::slug("{$mappedValues->get('name')}-{$activityYear}"),
            'tagline' => $mappedValues->get('tagline'),
            'location' => $mappedValues->get('location'),
            'location_address' => $mappedValues->get('location_address'),
            'start_date' => $activityStart,
            'end_date' => $activityEnd,
            'is_public' => false,
            'published_at' => $publishAt,
        ];

        Log::debug('Resolved raw data {row} to {data}', [
            'row' => $row,
            'data' => $proposedData,
        ]);

        // Verify data
        $validator = Validator::make($proposedData, self::VALIDATION_RULES);

        // Skip this row if the validation fails
        if ($validator->fails()) {
            Log::info('Skipping event {name} ({slug}), data invalid.', [
                'name' => $proposedData['name'],
                'slug' => $proposedData['slug'],
                'messages' => $validator->messages(),
            ]);

            throw new InvalidArgumentException($validator->messages()->first(), 400);
        }

        Log::info('Creating activity {name} from import', [
            'name' => $proposedData['name'],
            'slug' => $proposedData['slug'],
        ]);

        // Skip if the activity already exists
        if (Activity::findBySlug($proposedData['slug'])) {
            Log::info('Activity already exists, skipping');

            return;
        }

        $activity = Activity::make($proposedData);
        if ($this->activityRole) {
            $activity->role()->associate($this->activityRole);
        }

        return $activity;
    }
}
