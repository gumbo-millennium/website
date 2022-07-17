<?php
$enrollment = Enroll::getEnrollment($activity);
$canEnroll = Enroll::canEnroll($activity);
?>

<div class="flex flex-row mb-4">
    <div class="flex flex-col items-center">
        {{-- Stable --}}
        @if ($enrollment !== null)
            <a href="{{ route('enroll.show', [$activity]) }}" class="btn btn--brand">
                Inschrijving beheren
            </a>
            @if ($activity->start_date > now())
                <a href="{{ route('enroll.transfer', [$activity]) }}" class="mt-2 text-gray-300">Overdragen</a>
            @endif
        @elseif (!$canEnroll)
            <a disabled href="#" class="btn btn--brand">
                Inschrijven
            </a>
        @else
            <a href="{{ route('enroll.create', [$activity]) }}" class="btn btn--brand">
                Inschrijven
            </a>
        @endif
    </div>
    <div class="hidden lg:block flex-grow"></div>
</div>
