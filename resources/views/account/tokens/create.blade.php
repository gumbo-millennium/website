<x-account-page :hideTitle="true" currentRoute="account.tokens.show">
  <div class="pb-2 mb-4 border-b border-gray-200 flex flex-row justify-between">
    <h1 class="text-2xl font-title">@lang('Create new API Token')</h1>
  </div>

  <p class="leading-loose mb-2">
    Typ hieronder de naam van je token in om een nieuwe token aan te maken.<br />
    De token wordt hierna <strong>1x getoond</strong>, daarna is de token niet meer te zien.
  </p>

  <form action="{{ route('account.tokens.store') }}" method="POST">
    @csrf

    <div class="mb-4">
      <x-input :label="__('Name of token')" name="name" required />
    </div>

    <x-button style="primary" type="submit">
      @lang('Create token')
    </x-button>
  </form>
</x-account-page>
