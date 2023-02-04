<div class="sticky top-0 inset-x-0 pb-2 sm:pt-5 z-20 h-10 -mb-10">
    <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8" role="alert">
      <div class="p-2 rounded-lg bg-brand-600 shadow-lg sm:p-3">
        <div class="flex flex-row items-center gap-3">
          {{-- Icon --}}
          <span class="hidden sm:flex p-2 rounded-lg bg-brand-800" role="none">
            <div class="w-6 text-center">
              <x-icon icon="solid/bell" class="h-6 text-white" />
            </div>
          </span>

          {{-- Label --}}
          <p class="font-medium text-white truncate flex-grow w-0 ml-3 sm:ml-0">
            <span class="inline md:hidden">
              Verifieer je e-mailadres!
            </span>
            <span class="hidden md:inline">
              Hey {{ Auth::user()->first_name }}, bevestig je nog even je e-mailadres?
            </span>
          </p>

          <form method="post" action="{{ url('auth/email/resend') }}" class="flex-shrink-0" data-action="verify-email">
            @csrf
            <x-button type="submit" color="light" size="small">
              <x-icon icon="solid/arrow-rotate-right" class="h-4 sm:hidden" role="none" />
              <span class="sr-only sm:not-sr-only">Opnieuw sturen</span>
            </x-button>
          </form>
        </div>
      </div>
    </div>
  </div>
