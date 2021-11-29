@extends('layout.main', ['hideFlash' => true])

@section('title', "Inschrijving overnemen - {$activity->name} - Gumbo Millennium")

@section('content')
<div class="bg-gray-50">
    @component('components.enroll.header', ['activity' => $activity, 'enrollment' => $enrollment])
    <div class="leading-relaxed text-lg flex flex-col gap-y-4">
        <p>
            Je kan via onderstaande knop de inschrijving van {{ $enrollment->user->first_name }} overnemen.<br />
            Dit kan tot {{ $activity->start_date->isoFormat('dddd DD MMMM, HH:mm') }}.
        </p>

        @if ($enrollment->price > 0)
        <p>
            Dit is een betaalde inschrijving, maar {{ $enrollment->user->first_name }} heeft al betaald.<br />
            Jullie mogen onderling de betaling regelen.
        </p>
        @endif
    </div>
    @endcomponent

    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">
        <div class="enroll-card">
            <h3 class="font-title text-3xl font-bold mb-4">Gegevens van de inschrijving</h3>
            <dl class="enroll-list mb-8">
                <dt>Activiteit</dt>
                <dd>{{ $enrollment->activity->name }}</dd>

                <dt>Aanvang activiteit</dt>
                <dd>{{ $enrollment->activity->start_date->isoFormat('ddd DD MMMM YYYY, HH:mm') }}</dd>

                <dt>Ticket</dt>
                <dd>{{ $enrollment->ticket->title }}</dd>

                <dt>Ticketprijs</dt>
                @if ($enrollment->ticket->total_price > 0)
                    <dd>{{ Str::price($enrollment->ticket->total_price) }}</dd>
                    <dd class="text-sm">(incl. {{ Str::price($enrollment->ticket->total_price - $enrollment->ticket->price) }} transactiekosten)</dd>
                @else
                    <dd>Gratis</dd>
                @endif
            </dl>

            @if ($enrollment->is_form_exportable === false)
            <div class="mb-8">
                <div class="notice notice--info notice--large">
                    <h3 class="notice__title">Persoonlijke gegevens in inschrijving</h3>
                    <p>
                        Bij deze inschrijving zijn persoonlijke gegevens in het formulier opgenomen.
                        Om deze te beschermen, worden deze gewist na overdracht van de inschrijving.
                    </p>
                </div>
            </div>
            @endif

            <form formaction="{{ $acceptUrl }}" method="POST">
                @csrf

                <div class="flex flex-col gap-4 md:flex-row-reverse">
                    <button type="submit" class="w-full btn btn--small m-0 btn--brand text-center">
                        @lang('Confirm Enrollment Transfer')
                    </button>

                    <a href="{{ route('activity.show', [$activity]) }}" type="submit" class="w-full btn btn--small m-0 text-center">
                        @lang('Cancel')
                    </a>
                </div>
            </form>
        </div>

        <div>
            <a href="{{ route('activity.show', [$activity]) }}">
                Terug naar activiteit
            </a>
        </div>
    </div>
</div>
@endsection
