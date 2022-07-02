<x-auth-page title="Wachtwoord vergeten">
  <x-sections.transparent-header title="Wachtwoord vergeten?"
    subtitle="Of heb je gewoon te lang niet ingelogd?" />

  @if (session('status'))
  <div class="notice" role="alert">
      {{ session('status') }}
  </div>
  @endif

  <p>Het overkomt iedereen wel eens, dat je je wachtwoord vergeet.</p>
  <p>Maar geen zorgen, tik hieronder gewoon je e-mailadres in, en dan sturen we je een mailtje om je wachtwoord te herstellen.</p>

  {{-- Render form --}}
  {!! form($form, ['class' => 'form']) !!}

  <p class="mt-8 text-gray-primary-1">De link die je ontvang is 1 uur lang geldig.</p>
</x-auth-page>
