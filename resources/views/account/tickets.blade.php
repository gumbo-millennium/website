<x-account-page title="Mijn tickets">
  <p class="leading-loose mb-2">
    Hieronder zie je de tickets die in je account zijn gekoppeld. Je kunt ze vanaf hier toevoegen
    aan Google Wallet, of de QR code bekijken.
  </p>

    <x-card-grid narrow>
      {{-- Add all events --}}
      @foreach ($activities as $activity)
        <x-cards.activity :activity="$activity">
          <x-slot name="lead">
            {{ $activity->enrollment->ticket->title }}
          </x-slot>

          <x-slot name="footer">
            <div class="flex flex-col lg:flex-row w-full gap-4">
              <x-button href="{{ route('enroll.show', $activity) }}" class="w-full" :disabled="$activity->end_date < now()">
                @if ($activity->enrollment->is_stable)
                  Beheren
                @else
                  Afronden
                @endif
              </x-button>
              @if ($googleWalletUrls->has($activity->id))
                <x-button href="{{ $googleWalletUrls->get($activity->id) }}" target="_blank" style="night" class="w-full">
                  Toevoegen aan Google Wallet
                </x-button>
              @endif
            </div>
          </x-slot>
        </x-cards.activity>
      @endforeach
    </x-card-grid>

</x-account-page>
