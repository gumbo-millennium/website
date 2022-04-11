<?php

declare(strict_types=1);

namespace App\Exports;

use App\Helpers\Str;
use App\Models\Activity;
use App\Models\Enrollment;
use App\Models\States\Enrollment as States;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;

class ActivityParticipantsExport implements FromCollection, WithHeadings, WithProperties
{
    private Activity $activity;

    private ?Collection $collection = null;

    private ?Collection $titles = null;

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    public function properties(): array
    {
        return [
            'title' => sprintf('Inschrijvingen voor %s', $this->activity->name),
            'company' => 'Gumbo Millennium',
        ];
    }

    final public function headings(): array
    {
        $this->prepareCollectionAndTitles();

        return $this->titles->values()->all();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    final public function collection()
    {
        $this->prepareCollectionAndTitles();

        return $this->collection;
    }

    protected function getActivity(): Activity
    {
        return $this->activity;
    }

    /**
     * Load data from DB and construct data and title rows.
     */
    final protected function prepareCollectionAndTitles(): void
    {
        // Only run once
        if ($this->collection != null) {
            return;
        }

        // Get enrollments
        $enrollments = $this->activity->enrollments()->whereState('state', [
            States\Paid::class,
            States\Confirmed::class,
            States\Seeded::class,
            States\Created::class,
        ])->with(['user', 'ticket'])->get();

        // Place confirmed after pending enrollments
        $isStable = fn (Enrollment $enrollment) => $enrollment->is_stable;
        $participants = Collection::make()
            ->concat($enrollments->filter($isStable)->sortBy('user.last_name'))
            ->concat($enrollments->reject($isStable)->sortBy('user.last_name'));

        // Construct real data
        $this->collection = $this->processCollectionRows($participants);
        $this->titles = $this->processCollectionTitles($participants);
    }

    /**
     * Construct Excel body rows.
     */
    protected function processCollectionRows(Collection $collection): Collection
    {
        return $collection->map(fn (Enrollment $enrollment) => [
            implode(', ', array_filter([$enrollment->user->last_name, $enrollment->user->insert])),
            $enrollment->user->first_name,
            $enrollment->user->email,
            __($enrollment->state?->title),
            $enrollment->ticket->title,
            Str::price($enrollment->total_price ?? 0),
        ]);
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
        ]);
    }
}
