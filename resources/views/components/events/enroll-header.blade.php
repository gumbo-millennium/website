<?php
$details = array_filter([
    $activity->location,
    $activity->human_readable_dates,
    $activity->price_range,
]);
?>
<div class="flex flex-row mb-8 gap-4">
    <div class="flex flex-col w-full gap-4">
        @if ($poster = $activity->poster)
        <figure>
            <img src="{{ image_asset($poster)->width(640)->height(128) }}" class="h-32 w-full object-cover" />
        </figure>
        @endif

        <h1 class="font-title text-3xl">{{ $activity->name }}</h1>

        <div class="flex flex-row justify-between items-start">
            <ul class="flex flex-wrap gap-4">
                @foreach ($details as $item)
                    <p class="text-lg">{{ $item }}</p>
                    @if (! $loop->last)
                        <p class="text-lg">•</p>
                    @endif
                @endforeach
            </ul>

            @if (isset($enrollment))
            <div class="flex-none px-2 rounded border border-black" data-countdown="{{ $enrollment->expire->toIso8601String() }}">
                {{ $enrollment->expire->diffForHumans() }}
            </div>
            @endif
        </div>
    </div>

    @if ($showCancel ?? false)
    <div>
        <a class="btn btn--small" href="{{ route('enroll.cancel', [$activity]) }}">
            Annuleren
        </a>
    </div>
    @endif
</div>
