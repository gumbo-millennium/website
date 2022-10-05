<?php

declare(strict_types=1);

namespace App\Excel\Exports;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

class ActivityParticipantsFullExport extends ActivityParticipantsExport
{
    /**
     * @param Collection|Enrollment[] $participants
     */
    protected function assignTitlesAndRows(Collection $participants, Activity $activity): void
    {
        $formFields = $participants
            ->map(fn (Enrollment $enrollment) => array_keys($enrollment->form ?? []))
            ->collapse()
            ->unique()
            ->values()
            ->all();

        $data = $participants
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

        $this->setRows($data);
        $this->setTitles([
            __('Name'),
            __('First name'),
            __('Email'),
            __('Status'),
            __('Ticket'),
            __('Price'),
            ...$formFields,
        ]);
    }
}
