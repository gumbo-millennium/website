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
  <form action="{{ route('account.grants') }}" method="post" class="space-y-5">
    @csrf

    @foreach ($grants as $id => $grant)
    <div class="relative flex items-start">
      <div class="flex items-center h-6">
        <input id="{{ $grant->key }}" aria-describedby="{{ $grant->key }}-description" name="{{ $grant->key }}" type="checkbox"
          class="focus:ring-brand-500 h-4 w-4 text-brand-600 border-gray-300 rounded" @if($user->hasGrant($grant->key)) checked @endif>
      </div>
      <div class="ml-3 space-y-2">
        <label for="{{ $grant->key }}" class="font-medium text-gray-900">{{ $grant->name }}</label>
        <p id="{{ $grant->key }}-description" class="text-gray-500 text-sm">
          {{ $grant->description }}
        </p>
      </div>
    </div>
    @endforeach

    <div class="flex items-start">
      <div class="w-4 mr-3"></div>
      <x-button style="primary" type="submit">
          Opslaan
      </x-button>
    </div>
  </form>
</x-account-page>
