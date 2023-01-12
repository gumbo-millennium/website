<x-auth-page title="Login">
  <x-sections.transparent-header title="Account aanmaken" subtitle="Maak maximaal gebruik van de Gumbo site, met een account." />

  <div class="grid grid-cols-1 gap-2">
    <p>
      Met een Gumbo account kan je tickets kopen voor activieiten, en krijg je (als je lid bent) toegang
      tot het documentensysteem, de webshop, besloten activiteiten en nog meer.
    </p>

    <p class="text-sm">
      Een Gumbo account is geen lidmaatschap.
    </p>

    {!! form($form, ['class' => 'form']) !!}
  </div>

</x-auth-page>
