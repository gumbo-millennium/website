<?php

declare(strict_types=1);

namespace App\Excel\Imports;

use App\Helpers\Str;
use App\Models\Activity;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EnrollmentBarcodeImport extends StringValueBinder implements FromCollection, ShouldAutoSize, ToModel, WithHeadingRow, WithHeadings, WithProperties, WithStyles
{
    public const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public const IMPORT_TYPES = [
        'codabar', 'code39', 'code128', 'ean8', 'ean13', 'qrcode', 'text',
    ];

    public const COLUMNS = [
        'id' => [
            'name' => 'ID',
            'source' => 'id',
            'rules' => [
                'required',
                'integer',
            ],
        ],
        'name' => [
            'name' => 'Name of Participant',
            'source' => 'user.name',
        ],
        'ticket' => [
            'name' => 'Ticket type',
            'source' => 'ticket.title',
        ],
        'state' => [
            'name' => 'Status',
            'source' => 'state.title',
        ],
        'barcode_type' => [
            'name' => 'Barcode Type',
            'default' => 'qrcode',
            'rules' => [
                'nullable',
                'in:codabar,code39,code128,ean8,ean13,qrcode,text',
            ],
        ],
        'barcode' => [
            'name' => 'Barcode',
            'format' => NumberFormat::FORMAT_TEXT,
            'rules' => [
                'required',
                'string',
            ],
        ],
    ];

    public function __construct(
        private Activity $activity,
        private ?Collection $enrollments = null,
    ) {
        // Intentionally left empty
    }

    public function collection(): Collection
    {
        $result = Collection::make();

        foreach ($this->getEnrollments() as $enrollment) {
            $row = [];

            foreach (self::COLUMNS as $column => $data) {
                // Get data from source, which can be a dot notation path. If unset, use default (if set).
                $row[] = (isset($data['source']) ? object_get($enrollment, $data['source']) : null) ?? $data['default'] ?? null;
            }

            $result->push($row);
        }

        return $result;
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
            'creator' => ($user = Request::user()) ? "Gumbo Millennium for {$user->name}" : 'Gumbo Millennium',
            'title' => "Barcode Reassignment for {$this->activity->name}",
            'description' => 'Change enrollment barcodes in bulk for enrollments.',
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
            ->map(fn ($heading) => Arr::get($row, (string) Str::of($heading)->lower()->snake()))
            ->map(fn ($value) => empty($value) ? null : (string) $value);

        // Get the enrollment corresponding with this entry
        $enrollment = $this->getEnrollments()->get($mappedValues->get('id'));
        if (! $enrollment) {
            return null;
        }

        $assignedBarcode = preg_replace('/[^a-z0-9-_]+/i', '', (string) $mappedValues->get('barcode'));
        if (! $assignedBarcode) {
            return null;
        }

        // Build object
        $localData = [
            'id' => $enrollment->id,

            'barcode' => $assignedBarcode,
            'barcode_type' => $mappedValues->get('barcode_type') ?? 'text',

            'ticket' => $enrollment->ticket?->title,
            'user' => $enrollment->user?->name,
        ];

        Log::debug('Resolved raw data {row} to {data}', [
            'row' => $row,
            'data' => $localData,
        ]);

        // Verify data
        $rules = collect(self::COLUMNS)->map(fn ($row) => $row['rules'] ?? null)->filter()->toArray();
        $validator = Validator::make($localData, $rules);

        // Skip this row if the validation fails
        if (! $validator->valid()) {
            Log::info('Skipping enrollment {id} ({name}), data invalid.', [
                'id' => $localData['id'],
                'name' => $localData['user'],
                'ticket' => $localData['ticket'],
                'messages' => $validator->messages(),
            ]);

            throw new InvalidArgumentException($validator->messages()->first(), 400);
        }

        Log::info('Updating barcode on {id}', [
            'id' => $localData['id'],
        ]);

        // Update the barcode fields
        $enrollment->fill([
            'barcode' => $localData['barcode'],
            'barcode_type' => $localData['barcode_type'],
            'barcode_manual' => true,
        ]);

        // Return the updated enrollment
        return $enrollment;
    }

    private function getEnrollments(): Collection
    {
        return $this->enrollments ??= $this->activity
            ->enrollments()
            ->with('user:id,first_name,insert,last_name,name')
            ->with('ticket:id,title')
            ->active()
            ->get()
            ->keyBy('id');
    }
}
