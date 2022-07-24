<x-account-page :hideTitle="true">
  <div class="pb-2 mb-4 border-b border-gray-200 flex flex-row justify-between">
    <h1 class="text-2xl font-title">@lang('API Tokens')</h1>
    <div>
      <x-button size="small" style="outline" :href="route('account.tokens.create')">
        @lang("Create token")
      </x-button>
    </div>
  </div>

  <p class="leading-loose mb-2">
    Met onderstaande API tokens heb jij toegang tot de Gumbo APIs.<br />
    Je kan een nieuwe token aanmaken, of oude verwijderen.
  </p>

  @if ($newToken)
  <div class="mb-2">
    <x-alert :dismissable="false">
      @lang("Copy your access token now, you won't be able to see it again.")
    </x-alert>
  </div>
  @endif

  @if ($tokens->count() > 0)
  <div class="rounded border border-gray-200 divider-y divider-gray-200">
    @foreach ($tokens as $token)
    @php($isNewToken = $newToken?->accessToken->id === $token->id)
    <div @class([
      'p-4',
      'bg-gray-50' => $isNewToken,
    ])>
      <form action="{{ route('account.tokens.destroy') }}" method="POST" class="float-right">
        @csrf
        @method('DELETE')
        <input type="hidden" name="token" value="{{ $token->id }}">

        <x-button size="small" style="outline" type="submit">
          Intrekken
        </x-button>
      </form>

      @if ($isNewToken)
      <div class="py-2 flex items-center">
        <x-icon icon="solid/check" class="text-brand-600 h-4 mr-2" />
        <code class="text-gray-800 text-monospace">{{ $newToken->plainTextToken }}</code>
      </div>
      @else
      <span class="flex flex-wrap items-center space-x-1">
        <strong>{{ $token->name }}</strong>
        <em class="text-gray-500 text-sm">
          <span>–</span>
          @foreach ($token->abilities as $ability)
            <span>{{ $ability }}</span>
          @endforeach
        </em>
      </span>

      <div>
        <span class="text-gray-800 text-sm">
          Aangemaakt op <time datetime="{{ $token->created_at->toIso8601String() }}" class="italic">{{ $token->created_at?->isoFormat('D MMM Y, HH:mm (z)') }}</time>
        </span>
      </div>
      @endif
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
