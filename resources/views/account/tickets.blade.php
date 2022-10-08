<x-account-page title="Mijn tickets">
  <p class="leading-loose mb-2">
    Hieronder zie je de tickets die in je account zijn gekoppeld. Je kunt ze vanaf hier toevoegen
    aan Google Wallet, of de QR code bekijken.
  </p>

    <x-card-grid>
      {{-- Add all events --}}
      @foreach ($activities as $activity)
        <x-cards.activity :activity="$activity">
          <x-slot name="footer">
            <x-button href="{{ route('enroll.show', $activity) }}" class="w-full">
              Beheren
            </x-button>
          </x-slot>
        </x-cards.activity>
      @endforeach
    </x-card-grid>

</x-account-page>
