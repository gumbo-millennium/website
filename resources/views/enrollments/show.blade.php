@extends('layout.main', ['hideFlash' => true])

@section('title', "Beheer inschrijving voor {$activity->name}")

@php($ticket = $enrollment->ticket)

@section('ticket-card')
  <h2 class="heading-2 text-white mt-0 mb-0">{{ $user->name }}</h2>

  <div class="mb-4 flex gap-4">
      <p class="text-brand-50 flex-grow">
        {{ $activity->name }}<br />
        {{ $ticket->title }}
      </p>
      <p class="text-brand-50 mb-4 flex-none">{{ Str::price($ticket->total_price) ?? __('Free') }}</p>
  </div>

  <x-enroll.barcode :enrollment="$enrollment" />
@endsection

@section('content')
<div class="bg-gray-50">
    <x-enroll.header :activity="$activity" :enrollment="$enrollment">
        <div class="leading-relaxed text-lg flex flex-col gap-y-4">
            <p>
                Je bent ingeschreven voor {{ $activity->name }}.
            </p>
            <p>
                Hieronder vind je de details van je inschrijving.
            </p>
        </div>
    </x-enroll.header>

    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">
      @if(Config::get('gumbo.features.barcodes'))
      <div class="md:mt-8 lg:mt-0 w-full lg:max-w-3xl lg:mx-auto">
        <div class="enroll-card bg-brand-800">
          @yield('ticket-card')
        </div>
      </div>
      @endif

      <div class="enroll-card prose">
          @include('enrollments.partials.enrollment-data')
      </div>
    </div>

<div class="grid grid-cols-1 gap-8 enroll-column pb-8 ">

        <hr class="mb-8 bg-gray-200" />

        <div class="grid grid-cols-1 gap-4 items-stretch text-center md:grid-cols-2">
            <div class="grid grid-cols-1 text-center max-w-1/2 md:flex-grow relative">
                @if ($enrollment->state instanceof \App\Models\States\Enrollment\Paid)
                <button class="btn" type="button" disabled>
                    Uitschrijven niet mogelijk
                </button>
                @else
                <button class="btn" form="unenroll-form" type="submit">
                    Uitschrijven
                </button>
                <form action="{{ route('enroll.cancel', [$activity]) }}" method="POST" id="unenroll-form">
                    @csrf
                </form>
                @endif
            </div>

            <div class="grid grid-cols-1 text-center max-w-1/2 md:flex-grow">
                @if ($enrollment->is_stable && Enroll::canTransfer($enrollment))
                    <a href="{{ route('enroll.transfer', [$activity]) }}" class="btn md:flex-grow">
                        Overdragen
                    </a>
                @else
                <button class="btn" type="button" disabled>
                    Overdragen niet mogelijk
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">
        <a href="{{ route('activity.show', [$activity]) }}">
            Terug naar activiteit
        </a>
    </div>
</div>
@endsection
