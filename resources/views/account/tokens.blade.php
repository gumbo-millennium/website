<x-account-page :hideTitle="true">
  <div class="pb-2 mb-4 border-b border-gray-200 flex flex-row justify-between">
    <h1 class="text-2xl font-title">@lang('API Tokens')</h1>
    <div>
      <x-button size="small" style="outline" :href="route('account.tokens.store')">
        Create token
      </x-button>
    </div>
  </div>

  <p class="leading-loose mb-2">
    Met onderstaande API tokens heb jij toegang tot de Gumbo APIs.<br />
    Je kan een nieuwe token aanmaken, of oude verwijderen.
  </p>

  @if ($tokens->count() > 0)
  <div class="rounded border border-gray-200 divider-y divider-gray-200">
    @foreach ($tokens as $token)
    <div class="p-4">
      <form action="{{ route('account.tokens.delete', $token) }}" method="POST" class="float-right">
        @csrf
        @method('DELETE')

        <x-button size="small" style="outline" type="submit">
          Intrekken
        </x-button>
      </form>

      <strong>{{ $token->name }}</strong>

      <div>
        <span class="text-gray-800">
          Aangemaakt op <time datetime="{{ $token->created_at->toIso8601String() }}" class="italic">{{ $token->created_at?->isoFormat('D MMM Y, HH:mm (z)') }}</time>
        </span>
      </div>
    </div>
    @endforeach
  </div>
  @else
  <div class="rounded border border-gray-200 text-center p-8 lg:py-16">
    <h3 class="mt-2 text-sm font-medium text-gray-900">Geen API tokens</h3>
    <p class="mt-1 text-sm text-gray-500">Je hebt nog geen tokens aangemaakt.</p>
  </div>
  @endif
</x-account-page>
