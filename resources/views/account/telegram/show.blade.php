<x-account-page>
  <p class="leading-loose mb-4">
    Haal het meeste uit de <a href="https://telegram.me/{{ $telegramBotUsername }}" target="blank" rel="noopener">Gumbot</a> door te koppelen met je Telegram account.<br />
    Koppelen met je Telegram account is optioneel, je kan altijd de koppeling weer verwijderen.
  </p>

  @if ($connected)
    <div class="flex flex-row my-16 md:mx-16">
        {{-- Telegram user --}}
        <div class="flex flex-col items-center w-1/3 flex-grow">
            <div class="h-20 w-20 rounded-full bg-blue-500 text-white mb-4 flex items-center justify-center">
                <x-icon icon="brands/telegram-plane" class="h-8" />
            </div>

            <div class="mb-4">
              <strong class="text-lg">{{ $telegramName }}</strong>
              <p class="text-sm text-gray-500">#{{ $user->telegram_id }}</p>
            </div>

            <form action="{{ route('account.tg.unlink') }}" method="POST" class="text-center">
              @csrf
              @method('DELETE')

              <x-button type="submit" style="outline">
                Loskoppelen
              </x-button>

            </form>
        </div>

        {{-- Arrow --}}
        <div class="flex flex-col items-center w-20 flex-none">
            <x-icon icon="solid/arrow-right" class="text-grey-primary-3 h-8 mt-6" />
        </div>

        {{-- Gumbo user --}}
        <div class="flex flex-col items-center w-1/3 flex-grow">
            <div class="h-20 w-20 rounded-full bg-brand-500 text-white mb-4 flex items-center justify-center">
                <x-icon icon="solid/user" class="h-8" />
            </div>

            <strong class="text-lg">{{ $user->first_name }}</strong>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
        </div>
    </div>
  @else
    @php($benefits = [
      'Geen limiet op je ingestuurde wist-je-datjes.',
      'Besloten activiteiten via de bot.',
      'Plazacam en andere webcams opvragen.',
      'Mis nooit meer een notificatie.',
    ])
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <div class="rounded border border-gray-200 p-4">
        <h2 class="font-title text-xl mb-4">Voordelen op een rijtje</h2>

        <ul role="list" class="divide-y divide-gray-200">
          @foreach ($benefits as $benefit)
          <li class="py-4 flex items-center">
            <x-icon icon="solid/check" class="flex-shrink-0 h-4 w-4 text-brand-500" />
            <span class="ml-3 text-base text-gray-500">{{ $benefit }}</span>
          </li>
          @endforeach
        </ul>
      </div>

      <div class="p-4 text-center rounded bg-gray-900 text-white relative md:flex flex-col items-center justify-center">
        <div class="absolute bottom-0 right-0 w-64 h-64 overflow-hidden text-gray-800">
          <x-icon icon="brands/telegram-plane" class="w-96 h-96" />
        </div>

        <div class="relative space-y-4">
          <h2 class="font-title text-2xl">Direct even koppelen?</h2>

          @if ($telegramBotUsername)
            <p class="text-gray-100">
              Druk op onderstaande knop om naar Telegram te gaan.
            </p>

            <x-button style="primary" target="_blank" rel="noopener" :href="$telegramLink">
              Koppelen
            </x-button>

            <p class="text-gray-200 text-sm">
              Als je op "start" klikt krijg je een link om je account te koppelen.
            </p>
          @else
            <x-alert level="danger" :dismissable="false">
                De website heeft een configuratiefout
            </x-alert>
          @endif

        </div>
      </div>
    </div>
  @endif
</x-account-page>
