<x-account-page title="Mijn tickets">
  <p class="leading-loose mb-2">
    Hieronder zie je de tickets die in je account zijn gekoppeld.
    Hier kan je de tickets downloaden of een overzicht inzien.
  </p>

  @if ($activities->isEmpty())
    <x-empty-state.message title="Geen tickets" data-content="ticket-state-empty" icon="solid/ticket-simple">
      Je hebt nog geen tickets gekocht, of al je tickets
      zijn voor evenementen die al zijn geweest.
    </x-empty-state.message>
  @endif

  <div class="grid grid-cols-1 gap-8">
    {{-- Add all events --}}
    @foreach ($activities as $activity)
        <?php
        $enrollment = $activity->enrollment;
        $ticket = $enrollment->ticket;
        ?>
      <div class="card rounded-lg grid grid-cols-1 md:grid-cols-[4fr_6fr]">
        {{-- Mobile image --}}
        @if($activity->poster)
          <picture class="w-full h-48 md:hidden">
            <img src="{{ image_asset($activity->poster)->preset('tile') }}" alt="{{ $activity->title }}" class="object-cover w-full h-full rounded-t-lg">
          </picture>
        @else
          <x-empty-state.image class="w-full h-48 md:hidden rounded-t-lg"/>
        @endif

        {{-- Title and actions --}}
        <div class="flex flex-col">
          <div class="p-6 leading-none flex-grow">
            <p class="mb-2 text-sm font-medium text-brand-600">{{ $ticket->title }}</p>

            <h2 class="font-title font-semibold text-gray-900 text-xl">
              <a href="{{ route('activity.show', $activity) }}">{{ $activity->name }}</a>
            </h2>

            <p class="mt-3 text-base text-gray-500">{{ $activity->tagline }}</p>
          </div>

          <div class="div p-6 grid grid-cols-1 gap-4">
            @if ($enrollment->is_stable)
              <x-button with-icon href="{{ route('enroll.show', $activity) }}" class="w-full" :disabled="$activity->end_date < now()">
                <x-icon icon="solid/circle-info" />
                Details
              </x-button>
              <x-button with-icon href="{{ route('account.tickets.download', $enrollment) }}" class="w-full" :disabled="$enrollment->pdfExists()">
                <x-icon icon="solid/file-arrow-down" />
                Download PDF
              </x-button>
            @elseif ($enrollment->active())
              <x-button href="{{ route('enroll.show', $activity) }}" class="w-full" :disabled="$activity->end_date < now()">
                Afronden
              </x-button>
            @endif
          </div>
        </div>

        {{-- Desktop picture --}}
        @if($activity->poster)
          <picture class="hidden w-full md:block">
            <img src="{{ image_asset($activity->poster)->preset('tile') }}" alt="{{ $activity->title }}" class="object-cover w-full h-full rounded-r-lg">
          </picture>
        @else
          <x-empty-state.image class="hidden w-full md:flex rounded-r-lg"/>
        @endif
      </div>
    @endforeach
  </div>

</x-account-page>
