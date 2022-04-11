<?php

declare(strict_types=1);

namespace App\Exports;

use App\Helpers\Str;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

class ActivityParticipantsMedicalExport extends ActivityParticipantsExport
{
    private array $formFields = [];

    /**
     * Construct Excel body rows.
     */
    protected function processCollectionRows(Collection $collection): Collection
    {
        $this->formFields = $formFields = $collection
            ->map(fn (Enrollment $enrollment) => array_keys($enrollment->form ?? []))
            ->collapse()
            ->unique()
            ->values()
            ->all();

        return $collection
            ->map(function (Enrollment $enrollment) use ($formFields) {
                $rowData = [
                    implode(', ', array_filter([$enrollment->user->last_name, $enrollment->user->insert])),
                    $enrollment->user->first_name,
                    $enrollment->user->email,
                    __($enrollment->state?->title),
                    $enrollment->ticket->title,
                    Str::price($enrollment->total_price ?? 0),
                ];

                $enrollmentForm = $enrollment->form ?? [];
                foreach ($formFields as $field) {
                    $rowData[] = $enrollmentForm[$field] ?? '';
                }

                return $rowData;
            });
    }

    /**
     * Construct Excel titles.
     */
    protected function processCollectionTitles(Collection $collection): Collection
    {
        return Collection::make([
            __('Name'),
            __('First name'),
            __('Email'),
            __('Status'),
            __('Ticket'),
            __('Price'),
            ...$this->formFields,
        ]);
    }
}
