@extends('layout.main')

@section('content')
{{-- Welcome --}}
<div class="bg-brand-700 py-16">
    <div class="container">
        <h1 class="text-4xl text-center mb-8">Welkom bij Gumbo Millennium</h1>
        <p class="text-xl text-white text-center">
            Dubbel L, dubbel N, <strong class="font-bold">dubbel genieten</strong>!
        </p>
    </div>
</div>

{{-- Upcoming --}}
@if ($nextEvent)
<div class="bg-brand-100 activity-block">
    <div class="container activity-block__container">
        <div class="row">
            <div class="col activity-block__date-col">
                <time datetime="{{ $nextEvent->start_date->toIso8601String() }}" class="activity-block__date">
                    <div class="activity-block__date-day">
                        {{ $nextEvent->start_date->day }}
                    </div>
                    <div class="activity-block__date-month">
                        {{ $nextEvent->start_date->isoFormat('MMM') }}
                    </div>
                </time>
            </div>
            <div class="col p-4">
                <h1>I am the content</h1>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
