@extends('layout.tailwind')

@section('content')
@foreach ($activities as $activity)
<article class="shadow py-4 px-6 mb-3 flex flex-row rounded-lg items-start">
    <time datetime="{{ $activity->event_start->toIso8601String() }}" class="rounded border border-brand-400 flex flex-col items-center py-2 px-4">
        <span class="text-brand-500 text-3xl leading-tight">{{ $activity->event_start->day }}</span>
        <span class="leading-none uppercase text-gray-500">{{ $activity->event_start->locale('nl')->shortMonthName }}</span>
    </time>
    <div class="flex-grow ml-4">
        <h3 class="text-2xl leading-tight">{{ $activity->title ?? $activity->name ?? 'Activity' }}</h3>
        <p>{{ $activity->description }}</p>
        <ul class="float-left clearfix list-none">
            @if ($activity->seats !== null && $activity->available_seats > 0)
            <li>@icon('seat', 'h-4') {{ trans_choice('views.activity.seats', $activity->available_seats) }}
            @elseif ($activity->seats !== null)
            <li>@icon('seat', 'h-4') Uitverkocht</li>
            @endif
        </ul>
    </div>
</article>
@endforeach
@endsection
