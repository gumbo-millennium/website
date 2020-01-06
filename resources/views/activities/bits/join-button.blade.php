@php
// Enrollment open
$isOpen = $activity->enrollment_open;
$places = $activity->available_seats;
$hasRoom = $places > 0;
$hasRoomMember = $activity->available_seats > 0;
$expireDate = $is_enrolled ? ($enrollment->expire ?? $enrollment->updated_at->addWeek()) : optional(null);
$expireText = $expireDate->isoFormat(($expireDate->diffInMonths() > 1) ? 'DD MMMM Y' : 'DD MMMM');
$expireIso = $expireDate->toIso8601String();

$nextAction = null;
if ($user && $is_enrolled && !$is_stable) {
    $nextAction = 'Inschrijving afronden';
    $nextState = $enrollment->wanted_state;
    if ($nextState instanceof App\Models\States\Enrollment\Seeded) {
        $nextAction = 'Gegevens invullen';
    } elseif ($nextState instanceof App\Models\States\Enrollment\Paid) {
        $nextAction = sprintf('%s betalen via iDeal', Str::price($enrollment->total_price));
    }
}
@endphp

<div class="flex flex-col items-center">
    @if ($user && $is_enrolled && $is_stable)
    <a href="{{ route('enroll.show', compact('activity')) }}" class="btn m-0 btn--brand">Beheer inschrijving</a>

    {{-- Remove link --}}
    @if ($isOpen)
    <a href="{{ route('enroll.delete', compact('activity')) }}" class="mt-2 text-gray-500">Uitschrijven</a>
    @endif
    @elseif ($user && $is_enrolled)
    <a href="{{ route('enroll.show', compact('activity')) }}" class="btn m-0 btn--brand">{{ $nextAction }}</a>
    <p class="text-gray-700 text-center text-sm">Afronden voor <time datetime="{{ $expireIso }}">{{ $expireText }}</time></p>

    {{-- Remove link --}}
    <a href="{{ route('enroll.delete', compact('activity')) }}" class="mt-2 text-gray-500">Uitschrijven</a>
    @elseif (!$hasRoom)
    <button class="btn m-0 btn--link" disabled>Uitverkocht</button>
    @elseif (!$isOpen)
    <button class="btn m-0 btn--link" disabled>Inschrijvingen gesloten</button>
    @else
    <form action="{{ route('enroll.create', compact('activity')) }}" method="post">
        @csrf
        <button type="submit" class="btn m-0 btn--brand">Inschrijven</button>
    </form>
    @endif
</div>
