<x-account-page title="Mijn account">
  <p class="leading-loose mb-2">
    Welkom bij je account. Hier kan je jouw account beheren en je gegevens inzien.
  </p>

  <h3 class="mt-8 heading-3">Herkende gegevens</h3>
  <p class="mb-8">
    Aan de hand van je e-mailadres hebben we de volgende gegevens opgehaald:
  </p>

  <dt class="grid grid-cols-1 space-y-4">
    <dt class="font-bold">Lidstatus</dt>
    <dd class="mb-4">{{ $isMember ? 'Lid' : 'Geen lid' }}</dd>

    <dt class="font-bold">Commissies en projectgroepen</dt>
    <dd class="mb-4">
      <div class="flex flex-row flex-wrap gap-2">
        @forelse ($recognizedRoles as $role)
          <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
            {{ $role }}
          </div>
        @empty
          <div class="flex-none">
            <span class="text-gray-500">Geen commissies of projectgroepen herkend</span>
          </div>
        @endforelse
      </div>

      <form class="mt-4" action="{{ route('account.request-update') }}" method="POST">
        @csrf

        <x-button type="submit">
          @lang('Request account update')
        </x-button>

      </form>
    </dd>
</x-account-page>
