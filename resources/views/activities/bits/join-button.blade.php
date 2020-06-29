@php
// Enrollment open
$isOpen = $activity->enrollment_open;
$isStable = optional($enrollment)->is_stable;
$places = $activity->available_seats;
$hasRoom = $places > 0;
$hasRoomMember = $activity->available_seats > 0;
$expireDate = $is_enrolled ? ($enrollment->expire ?? $enrollment->updated_at->addWeek()) : optional(null);
$expireText = $expireDate->isoFormat(($expireDate->diffInMonths() > 1) ? 'DD MMMM Y' : 'DD MMMM');
$expireIso = $expireDate->toIso8601String();

$nextAction = null;
if ($user && $is_enrolled && !$isStable) {
    $nextAction = 'Inschrijving afronden';
    $nextState = $enrollment->wanted_state;
    if ($nextState instanceof App\Models\States\Enrollment\Seeded) {
        $nextAction = 'Gegevens invullen';
    } elseif ($nextState instanceof App\Models\States\Enrollment\Paid) {
        $nextAction = sprintf('%s betalen via iDeal', Str::price($enrollment->total_price));
    }
}

$whenOpen = null;
if (!$isOpen && $activity->enrollment_start > now()) {
    $whenOpen = $activity->enrollment_start->isoFormat('[Opent op] D MMM [om] HH:mm');
} elseif ($isOpen && $activity->enrollment_end > now()) {
$whenOpen = $activity->enrollment_end->isoFormat('[Sluit op] D MMM [om] HH:mm');
}
@endphp

<div class="flex flex-row mb-4">
<div class="flex flex-col items-center">
    {{-- Stable --}}
    @if ($user && $is_enrolled && $isStable)
    <div class="btn m-0 btn--disabled">Ingeschreven</div>
    @if ($isOpen && $user->can('unenroll', $enrollment))
    <a href="{{ route('enroll.remove', compact('activity')) }}" class="mt-2 text-gray-secondary-3">Uitschrijven</a>
    @endif

    {{-- Instable --}}
    @elseif ($user && $is_enrolled)
    <a href="{{ route('enroll.show', compact('activity')) }}" class="btn m-0 btn--brand">{{ $nextAction }}</a><br />
    <p class="text-gray-primary-2 text-center text-sm">Afronden voor <time datetime="{{ $expireIso }}">{{ $expireText }}</time></p>
    <a href="{{ route('enroll.remove', compact('activity')) }}" class="mt-2 text-gray-secondary-3">Uitschrijven</a>

    {{-- Fully booked --}}
    @elseif (!$hasRoom)
    <div class="btn m-0 btn--disabled" disabled>Uitverkocht</div>

    {{-- Closed --}}
    @elseif (!$isOpen)
    <div class="btn m-0 btn--disabled" disabled>Inschrijvingen gesloten</div>
    @if ($whenOpen)
    <div class="mt-2 text-gray-secondary-3">{{ $whenOpen }}</div>
    @endif

    {{-- No verified e-mail --}}
    @elseif ($user && !$user->hasVerifiedEmail() && $activity->is_free)
    <form action="{{ route('activity.verify-email', compact('activity')) }}" method="post">
        @csrf
        <p class="mb-2 text-gray-secondary-3">Je moet je e-mailadres eerst bevestigen.</p>
        <button type="submit" class="btn m-0">Bevestig e-mailadres</button>
    </form>

    {{-- Open --}}
    @else
    <form action="{{ route('enroll.create', compact('activity')) }}" method="post">
        @csrf
        <button type="submit" class="btn m-0 btn--brand">Inschrijven</button>
    </form>
    @if ($whenOpen)
    <div class="mt-2 text-gray-secondary-3">{{ $whenOpen }}</div>
    @endif
    @endif
</div>
<div class="hidden lg:block flex-grow"></div>
</div>
