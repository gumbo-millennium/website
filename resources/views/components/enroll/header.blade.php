<?php
$details = array_filter([
    $activity->location,
    $activity->human_readable_dates,
    $activity->price_range,
]);

$enrollment ??= null;
$blurredImage = image_asset($activity->poster)->square(1920/2)->height(256/2)->blur(40);

$canExpire = $timeout = null;

if ($enrollment) {
    $canExpire = ! $enrollment->state->isStable();

    $timeout = sprintf(
        '%02d:%02d:%02d',
        $enrollment->expire->diffInHours(),
        $enrollment->expire->diffInMinutes() % 60,
        $enrollment->expire->diffInSeconds() % 60
    );
}
?>
<div class="relative">
    @if ($activity->poster)
    <picture class="absolute inset-0 h-64 bg-brand-900">
        <source srcset="{{ $blurredImage->webp() }}" type="image/webp">
        <source srcset="{{ $blurredImage->jpeg() }}" type="image/jpeg">
        <img class="w-full h-64 object-cover" src="{{ $blurredImage }}" alt="{{ $activity->name }}">
    </picture>
    @else
    <div class="absolute inset-0 h-3/4 bg-brand-900"></div>
    @endif

    <div class="relative z-10 enroll-column pt-8 lg:pt-16">
        @if (flash()->message)
        <div class="mb-4 mt-0" role="alert">
            <div class="notice {{ flash()->class }} bg-white mt-0">
                <p>{{ flash()->message }}</p>
            </div>
        </div>
        @endif

        <div class="enroll-card">
            <div class="flex flex-col md:flex-row mb-8 gap-4">
                <div class="flex flex-col w-full gap-4">
                    <div class="flex flex-row justify-between items-center">
                        <h1 class="font-title text-3xl">{{ $activity->name }}</h1>

                        @if ($enrollment && $canExpire)
                        <div class="hidden lg:flex flex-none px-2 rounded border border-black" data-complete-class="border-red-600"
                            data-countdown="{{ $enrollment->expire->toIso8601String() }}">
                            {{ $timeout }}
                        </div>
                        @endif
                    </div>

                    @if (! $enrollment)
                    <div class="flex flex-row justify-between items-start">
                        <ul class="flex flex-wrap gap-4">
                            @foreach ($details as $item)
                            <p class="text-lg">{{ $item }}</p>
                            @if (! $loop->last)
                            <p class="text-lg hidden lg:block">•</p>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                <div class="flex flex-col items-center lg:hidden">
                    @if ($enrollment && $canExpire)
                    <div class="flex-none px-2 rounded border border-black" data-complete-class="border-red-600"
                        data-countdown="{{ $enrollment->expire->toIso8601String() }}">
                        {{ $timeout }}
                    </div>
                    @endif
                </div>
            </div>

            @if ($slot)
            <div class="mt-4">
                {{ $slot }}
            </div>
            @endif
        </div>
    </div>
</div>
