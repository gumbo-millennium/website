@extends('layout.main')

@section('title', "Inschrijven voor {$activity->name}")

@section('content')
<div class="container container--sm py-8">
    @component('components.events.enroll-header', ['activity' => $activity, 'enrollment' => $enrollment])
    @endcomponent

    <div class="grid grid-cols-1 gap-4 mb-4">
        <div class="mb-4 leading-relaxed text-lg flex flex-col gap-y-4">
            <p>
                Als laatste stap moet je nog even {{ Str::price($activity->price) }} betalen.
            </p>
            <p>
                Dit kan online <strong>exclusief</strong> via iDEAL. Wil je liever betalen via overboeking
                of via een andere afspraak met het bestuur? Neem dan contact op met het bestuur.
            </p>
        </div>

        <div>
            <h3 class="font-title font-bold text-2xl">Kies een vet populaire bank</h3>
            <p class="text-gray-400 text-lg">(dat vinden we niet raar, enkel bijzonder)</p>
        </div>

        <form action="{{ route('enroll.payStore', [$activity]) }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            @csrf

            @foreach ($highlightedBanks as $code => $label)
            <button type="submit" name="bank" value="{{ $code }}" class="btn btn--brand my-0">
                {{ $label }}
            </button>
            @endforeach
        </form>

        <form action="{{ route('enroll.payStore', [$activity]) }}" method="POST" class="grid grid-cols-1 gap-4">
            @csrf

            <div class="pt-4 border-t border-gray-300">
                <h3 class="font-title font-bold text-2xl">Of gewoon jóuw bank</h3>
                <p class="text-gray-400 text-lg">(wel zo comfortabel)</p>
            </div>

            <select name="bank" class="form-select w-full">
                @foreach ($banks as $code => $label)
                <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn--brand my-0">
                {{ Str::price($activity->price) }} betalen via iDEAL
            </button>
        </form>
    </div>

    <form action="{{ route('activity.show', [$activity]) }}" method="POST" class="w-full grid grid-cols-1 lg:block">
        @csrf
        <button type="submit" class="btn btn--small text-center">
            Annuleren en terug naar activiteit
        </button>
    </form>
</div>
@endsection
