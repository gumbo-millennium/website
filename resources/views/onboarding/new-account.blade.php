<x-auth-page title="Login">
  <x-sections.transparent-header title="Registratie voltooid"
    subtitle="Bedankt voor het aanmaken van een account." />

  <p>
      Je account is aangemaakt, maar voordat je je kan aanmelden voor activiteiten en het
      documentensysteem kunt bekijken (als je lid bent), moet je eerst even je e-mailadres
      bevestigen.
  </p>
  <div class="my-4 p-4 border border-brand-500 rounded">
      <p>
          Klik op <strong>de link in je mail</strong> om je e-mailadres te bevestigen.
      </p>
  </div>

  <p>Om door te gaan naar de website, kan je hieronder klikken.</p>

  <div class="mt-4">
    <x-button href="{{ $nextUrl }}" style="primary">Doorgaan</x-button>
  </div>

</x-auth-page>
