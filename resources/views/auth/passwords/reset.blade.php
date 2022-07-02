<x-auth-page title="Wachtwoord herstellen">
  <x-sections.transparent-header title="Wachtwoord herstellen"
    subtitle="Je bent nog maar 2 × 10 tekens verwijderd van een nieuw wachtwoord" />

  <p>Je hebt het zware werk al gehad. Kijk even na of je e-mailadres klopt.</p>

  {{-- Render form --}}
  {!! form_start($form, ['class' => 'form']) !!}
  {!! form_until($form, 'email') !!}

  <p class="mt-8">Als je e-mailadres klopt, tik dan hieronder 2x hetzelfde wachtwoord in van <strong>minimaal 10 tekens</strong></p>

  {!! form_end($form) !!}
</x-auth-page>
