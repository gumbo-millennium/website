@extends('layout.main')

@section('content')
{{-- Welcome --}}
<div class="header">
    <div class="container header__container">
        <h1 class="header__title">Welkom bij Gumbo Millennium</h1>
        <p class="header__subtitle">
            Dubbel L, dubbel N, <strong class="font-bold">dubbel genieten</strong>!
        </p>
    </div>
</div>

{{-- Upcoming --}}
@if ($nextEvent)
<div class="bg-brand-50 after-header">
    @include('activities.bits.list-item', ['activity' => $nextEvent])
</div>
@endif
@endsection
