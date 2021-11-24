<div class="flex flex-row mb-8 gap-4">
    <div class="flex flex-col gap-4">
        @if ($poster = $activity->poster)
        <figure class="h-32 w-full overflow-hidden object-cover">
            <img src="{{ image_asset($poster)->width(640)->height(128) }}" </figure>
            @endif
            <h1 class="font-title text-3xl">{{ $activity->name }}</h1>

            <ul class="flex flex-wrap gap-4">
                <p class="text-lg">{{ $activity->location }}</p>
                <p class="text-lg">•</p>
                <p class="text-lg">{{ $activity->human_readable_dates }}</p>
                <p class="text-lg">•</p>
                <p class="text-lg">{{ $activity->price_label }}</p>
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
