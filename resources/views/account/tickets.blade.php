<x-account-page title="Mijn tickets">
    <p class="leading-loose mb-2">
        Hieronder zie je de tickets die in je account zijn gekoppeld. Je kunt ze vanaf hier toevoegen
        aan Google Wallet, of de QR code bekijken.
    </p>

    @if ($activities->isEmpty())
      <x-empty-state.message title="Geen tickets" data-content="ticket-state-empty" icon="solid/ticket-alt">
        Je hebt nog geen tickets gekocht, of al je tickets
        zijn voor evenementen die al zijn geweest.
      </x-empty-state.message>
    @endif

    <x-card-grid narrow>
      {{-- Add all events --}}
      @foreach ($activities as $activity)
        @php($enrollment = $activity->enrollment)
        <x-cards.activity :activity="$activity">
          <x-slot name="lead">
            {{ $enrollment->ticket->title }}
          </x-slot>

          <x-slot name="footer">
            <div class="flex flex-col lg:flex-row w-full gap-4">
              @if ($enrollment->is_stable)
              <x-button href="{{ route('enroll.show', $activity) }}" class="w-full" :disabled="$activity->end_date < now()">
                Beheren
              </x-button>
              @if ($activity->end_date > now())
                <x-button href="{{ route('account.tickets.wallet', $activity) }}" target="_blank" style="night" class="w-full">
                  Toevoegen aan Google Wallet
                </x-button>
              @endif
              @elseif ($enrollment->active())
              <x-button href="{{ route('enroll.show', $activity) }}" class="w-full" :disabled="$activity->end_date < now()">
                Afronden
              </x-button>
              @endif
            </div>
          </x-slot>
        </x-cards.activity>
      @endforeach
    </x-card-grid>

</x-account-page>
