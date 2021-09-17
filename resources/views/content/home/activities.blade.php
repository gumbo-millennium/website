{{-- Upcoming --}}
@if (!empty($nextEvents))
<div class="container pt-8">
    <p class="text-center text-gray-primary-1 mb-4">Altijd iets te doen</p>
    <h2 class="text-3xl text-medium font-title mb-8 text-center">Binnenkort bij Gumbo Millennium</h2>
    {{-- Activity cards --}}
    <div class="card-grid">
        @foreach ($nextEvents as $activity)
        <div class="card-grid__item">
            @include('activities.bits.single')
        </div>
        @endforeach
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('activity.index') }}" class="btn btn--brand my-0">
            Bekijk alle activiteiten
        </a>
    </div>
</div>
@endif
