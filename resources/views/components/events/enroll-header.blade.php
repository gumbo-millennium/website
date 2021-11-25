<?php
$details = array_filter([
    $activity->location,
    $activity->human_readable_dates,
    $activity->price_range,
]);
?>
<div class="flex flex-row mb-8 gap-4">
    <div class="flex flex-col gap-4">
        @if ($poster = $activity->poster)
        <figure>
            <img src="{{ image_asset($poster)->width(640)->height(128) }}" class="h-32 w-full object-cover" />
        </figure>
        @endif

        <h1 class="font-title text-3xl">{{ $activity->name }}</h1>

        <ul class="flex flex-wrap gap-4">
            @foreach ($details as $item)
                <p class="text-lg">{{ $item }}</p>
                @if (! $loop->last)
                    <p class="text-lg">•</p>
                @endif
            @endforeach
        </ul>
    </div>

    @if ($showCancel ?? false)
    <div>
        <a class="btn btn--small" href="{{ route('enroll.cancel', [$activity]) }}">
            Annuleren
        </a>
    </div>
    @endif
</div>
