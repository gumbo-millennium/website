<x-account-page activeRoute="account.tg.show">
  <p class="leading-loose mb-4">
    Haal het meeste uit de Gumbot door te koppelen met je Telegram account.<br />
    Koppelen met je Telegram account is optioneel, je kan altijd de koppeling weer verwijderen.
  </p>

  {{-- Display --}}
  <div class="flex flex-row my-16 md:mx-16">
    {{-- Telegram user --}}
    <div class="flex flex-col items-center w-1/3 flex-grow">
      <div class="h-20 w-20 rounded-full bg-blue-500 text-white mb-4 flex items-center justify-center">
        <x-icon icon="brands/telegram-plane" class="h-8" />
      </div>

      <strong class="text-lg">{{ $telegramName }}</strong>
      <p class="text-sm text-gray-500">#{{ $telegramId }}</p>
    </div>

    {{-- Arrow --}}
    <div class="flex flex-col items-center w-20 flex-none">
      <x-icon icon="solid/arrow-right" class="text-grey-primary-3 h-8 mt-6" />
    </div>

    {{-- Gumbo user --}}
    <form method="POST" action="{{ route('account.tg.link') }}" class="flex flex-col items-center w-1/3 flex-grow">
      @csrf

      <div class="h-20 w-20 rounded-full bg-brand-500 text-white mb-4 flex items-center justify-center">
        <x-icon icon="solid/user" class="h-8" />
      </div>

      <strong class="text-lg">{{ $user->first_name }}</strong>
      <p class="text-sm text-gray-500">{{ $user->email }}</p>

      <div class="mt-4">
        <x-button type="submit" style="primary">Koppelen</x-button>
      </div>
    </form>
  </div>
</x-account-page>
