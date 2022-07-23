<x-account-page>
<p>
  Je kan hieronder jouw bekende gegevens inzien en aanpassen.<br />
  Indien je account is herkend als Gumbo lid, kan je sommige velden niet aanpassen,
  ga hiervoor naar het bestuur.
</p>

{{-- Render form --}}
{!! form_start($form, ['class' => 'form']) !!}
{!! form_until($form, 'after_name') !!}

<hr class="border-gray-300 my-4" />

<h3 class="text-xl font-normal">Profiel informatie</h3>
<p class="mb-8">Pas hieronder je e-mailadres en alias aan. Het wijzigen van je e-mailadres blokkeert je account totdat je deze valideert.</p>

{{-- {!! form_rest($form) !!} --}}
{!! form_end($form) !!}

</x-account-page>
