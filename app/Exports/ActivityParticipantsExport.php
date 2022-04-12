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

abstract class ActivityParticipantsExport implements FromCollection, WithHeadings, WithProperties
{
    private Activity $activity;

    private ?Collection $titles = null;

    private ?Collection $rows = null;

    /**
     * Load participants in proper order.
     */
    private static function determineParticipants(Activity $activity): Collection
    {
        // Get enrollments
        $enrollments = $activity->enrollments()->whereState('state', [
            States\Paid::class,
            States\Confirmed::class,
            States\Seeded::class,
            States\Created::class,
        ])->with(['user', 'ticket'])->get();

        // Place confirmed first, pending enrollments second
        $isStable = fn (Enrollment $enrollment) => $enrollment->is_stable;

        return Collection::make()
            ->concat($enrollments->filter($isStable)->sortBy('user.last_name'))
            ->concat($enrollments->reject($isStable)->sortBy('user.last_name'));
    }

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;

        $participants = self::determineParticipants($activity);

        $this->assignTitlesAndRows($participants, $activity);
    }

    /**
     * @param Collection|Enrollment[] $participants
     */
    abstract protected function assignTitlesAndRows(Collection $participants, Activity $activity): void;

    public function properties(): array
    {
        return [
            'title' => sprintf('Inschrijvingen voor %s', $this->activity->name),
            'company' => 'Gumbo Millennium',
        ];
    }

    final public function headings(): array
    {
        return $this->titles->values()->all();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    final public function collection()
    {
        return $this->rows;
    }

    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    protected function setTitles(iterable $titles): self
    {
        $this->titles = Collection::make($titles);

        return $this;
    }

    protected function setRows(iterable $rows): self
    {
        $this->rows = Collection::make($rows);

        return $this;
    }

    protected function getActivity(): Activity
    {
        return $this->activity;
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
