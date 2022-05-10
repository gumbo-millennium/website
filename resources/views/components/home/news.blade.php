<x-sections.card-section>
  <x-slot name="title">De laatste updates</x-slot>
  <x-slot name="subtitle">
    De laatste updates vanuit Gumbo, gewoon hier te lezen!
  </x-slot>

  @foreach ($items as $item)
  <x-cards.news :item="$item" />
  @endforeach

  <x-slot name="callToAction">
    <x-button color="primary" href="{{ route('news.index') }}">
      Bekijk alle updates
    </x-button>
  </x-slot>
</x-sections.card-section>
