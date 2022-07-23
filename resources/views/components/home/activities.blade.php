<x-sections.card-section>
  <x-slot name="title">Allemaal activiteiten! Ga je mee?</x-slot>
  <x-slot name="subtitle">
    Gumbo ❤️ activiteiten. Van een themarijke soosavond tot het befaamde Landhuisweekend, bij ons is het gezellig!
  </x-slot>

  @foreach ($items as $item)
  <x-cards.activity :activity="$item" />
  @endforeach

  <x-slot name="callToAction">
    <x-button style="primary" href="{{ route('activity.index') }}">
      Bekijk alle activiteiten
    </x-button>
  </x-slot>
</x-sections.card-section>
