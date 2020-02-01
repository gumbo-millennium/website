@extends('layout.main')

@section('content')
{{-- Welcome --}}
<div class="header header--clipped">
    <div class="container header__container">
        <h1 class="header__title">Welkom bij Gumbo Millennium</h1>
        <p class="header__subtitle">
            Dubbel L, dubbel N, <strong class="font-bold">dubbel genieten</strong>!
        </p>
    </div>
</div>

{{-- Upcoming --}}
@if (!empty($nextEvents))
<div class="container pt-8">
    <h2 class="text-3xl text-normal mb-8">Binnenkort bij Gumbo Millennium</h2>
    {{-- Activity cards --}}
    <div class="activity-grid">
        @foreach ($nextEvents as $activity)
        <div class="activity-grid__item">
            @include('activities.bits.single')
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
