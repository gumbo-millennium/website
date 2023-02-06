<?php declare(strict_types=1);
$title = 'Aankomende activiteiten';
$subtitle = 'Binnenkort op de agenda bij Gumbo Millennium';
if ($past) {
    $title = 'Afgelopen activiteiten';
    $subtitle = 'Overzicht van afgelopen activiteiten.';
}

// Get first activity
$firstActivity = $past ? null : $activities->first();
?>
<x-page title="{{ $title }}" hide-flash="true">
  <x-sections.header
    :title="$title"
    :subtitle="$subtitle"
    :crumbs="['/' => 'Home']"
  >
    <x-slot name="buttons">
      @if ($past)
        <x-button size="small" style="light"  href="{{ route('activity.index') }}">
          Aankomende activiteiten
        </x-button>
      @else
        <x-button size="small" style="light" href="{{ route('activity.index', ['past' => true]) }}">
          Afgelopen activiteiten
        </x-button>
      @endif
    </x-slot>
  </x-sections.header>

  <x-container space="small">
    @if (empty($activities))
        <div class="text-center p-16">
            <h2 class="text-2xl font-normal text-center">Geen activiteiten</h2>
            <p class="text-center text-lg">De agenda is verdacht leeg. Kom later nog eens kijken.</p>
        </div>
    @else
      @if (!$past)
      <div class="text-lg mt-6">
        <p class="text-lg font-medium">
          Bij Gumbo Millennium zit je goed voor activiteiten. Van een themarijke soosavond tot een befaamd weekend weg met de gehele
          vereniging, bij ons zit je elke week goed voor gezelligheid.
        </p>

        <p class="mt-5">
          Neem snel een kijkje in onze agenda hieronder.
        </p>
      </div>
      @endif

      <x-card-grid>
        {{-- Add all events --}}
        @foreach ($activities as $activity)
        <x-cards.activity :activity="$activity" />
        @endforeach
      </x-card-grid>

      <div class="mt-5 w-full grid">
        {{ $activities->links() }}
      </div>
    @endif
  </x-container>

  <x-activities.ical-link />

</x-page>

