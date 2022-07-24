@extends('layout.main', ['hideFlash' => true])

@section('title', "Inschrijving overdragen - {$activity->name} - Gumbo Millennium")

@section('content')
<div class="bg-gray-50">
    <x-enroll.header :activity="$activity" :enrollment="$enrollment">
        <div class="leading-relaxed text-lg flex flex-col gap-y-4">
            <p>
                Je kunt je inschrijving voor {{ $activity->name }} overdragen tot aanvang van de activiteit. Daarna is
                overdragen niet meer mogelijk.
            </p>
            <p>
                Deel onderstaande link met de persoon die je wilt uitnodigen. Diegene kan dan na inloggen de inschrijving
                overnemen.
            </p>
        </div>
    </x-enroll.header>

    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">
        <div class="enroll-card">
            <h3 class="font-title font-bold text-2xl mb-2">
                Jouw overdraaglink
            </h3>

            <p>
                Hieronder staat de link om jouw inschrijving over te dragen. Deze is te gebruiken
                tot {{ $activity->start_date->isoFormat('dddd DD MMMM, HH:mm') }}.
            </p>

            <div class="my-4">
                <div class="rounded bg-gray-100 px-4 py-2 text-center">
                    @if ($transferLink)
                    <a href="{{ $transferLink }}" target="_blank" data-action="share"
                        data-share-url="{{ $transferLink }}" data-share-text="{{ $transferText }}">
                            {{ $transferLink }}
                        </a>
                    @else
                    <span class="text-gray-700">
                        @lang("You haven't requested a transfer code yet")
                    </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-row justify-stretch items-center md:justify-end gap-4 w-full">
                @if ($enrollment->transfer_secret)
                <button type="submit" form="transfer-update" class="w-1/2 md:w-auto btn btn--small my-0">Vernieuwen</button>
                <button type="submit" form="transfer-delete" class="w-1/2 md:w-auto btn btn--small my-0">Deactiveren</button>
                @else
                <button type="submit" form="transfer-update" class="w-1/2 md:w-auto btn btn--danger btn--small my-0">Genereren</button>
                @endif
            </div>
        </div>

        <div>
            <a href="{{ route('enroll.show', [$activity]) }}">
                Terug naar inschrijving
            </a>
        </div>
    </div>

    {{-- Forms --}}
    <form class="hidden" id="transfer-update" name="transfer-update"
        action="{{ route('enroll.transfer', [$activity]) }}" method="post">
        @csrf
    </form>
    <form class="hidden" id="transfer-delete" name="transfer-delete"
        action="{{ route('enroll.transfer', [$activity]) }}" method="post">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
