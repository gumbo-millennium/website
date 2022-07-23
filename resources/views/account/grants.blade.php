<x-account-page>
  <p class="mb-2">
      Hieronder zie je alle toestemmingen die je hebt gegeven. Je kan deze altijd bijwerken als je wilt.
  </p>

  <p>
      <strong>Let op:</strong>
      Het kan zijn dat het even duurt voordat je toestemmingen overal zichtbaar zijn, sommige pagina's worden
      een tijdje lokaal opgeslagen.
  </p>
  <p>
      Als je wijzigingen na 24 uur nog steeds niet zijn doorgevoerd, laat het dan weten!
  </p>

  {{-- Render form --}}
  {!! form($form, ['class' => 'form']) !!}
</x-account-page>
