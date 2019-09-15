@extends('layout.tailwind')

@section('content')
<div class="block md:flex flex-row flex-wrap">
@foreach ($activities as $activity)
<div class="activity-wrapper">
    <article class="activity">
        <div class="activity__image" role="presentation">
            @if ($activity->image->exists())
            <img class="activity__image-inner"
                alt="Foto bij {{ $activity->title }}"
                src="{{ $activity->image->url('cover') }}"
                srcset="{{ $activity->image->url('cover') }} 1x, {{ $activity->image->url('cover@2x') }} 2x" />
            @else
            <div class="activity__image-inner activity__image-inner--empty"></div>
            @endif
        </div>
        <div class="activity__content">
            <time datetime="{{ $activity->start_date->toIso8601String() }}" class="activity__date">
                <span class="activity__date-day">{{ $activity->start_date->day }}</span>
                <span class="activity__date-month">{{ $activity->start_date->locale('nl')->shortMonthName }}</span>
            </time>
            <div class="flex-grow ml-4 self-center">
                <h3 class="text-2xl leading-tight">
                    <a class="text-brand-400 underline hover:no-underline focus:no-underline hover:text-brand-600 focus:text-brand-600" href="{{ route('activity.show', ['activity' => $activity]) }}">{{ $activity->title ?? $activity->name ?? 'Activity' }}</a>
                </h3>
                <p>{{ $activity->tagline ?? Illuminate\Support\Str::limit($activity->description, 30) }}</p>
                <ul class="float-left clearfix list-none">
                @if ($activity->seats !== null && $activity->available_seats > 0)
                    <li>@icon('seat', 'h-4') {{ trans_choice('views.activity.seats', $activity->available_seats) }}
                @elseif ($activity->seats !== null)
                    <li>@icon('seat', 'h-4') Uitverkocht</li>
                @endif
                </ul>
            </div>
        </div>
    </article>
</div>
@endforeach
</div>
@endsection
