{{-- Start form --}}
{!! form_start($form, ['class' => 'form']) !!}

{{-- Part 1: Name --}}
<div class="mb-8">
    <h3 class="text-xl font-normal">Wat is je naam?</h3>
    <p>Omdat dit een lidmaatschap betreft, willen wij graag je officiële naam weten, zoals in je paspoort staat.</p>
</div>
{!! form_until($form, 'last-name') !!}

{{-- Part 2: Contact details --}}
<div class="mb-4 mt-8">
    <h3 class="text-xl font-normal">Hoe kunnen wij je bereiken?</h3>
    <p>We hebben graag een e-mailadres en telefoonnummer van onze leden, vul deze hieronder in.</p>
</div>
{!! form_until($form, 'phone') !!}

{{-- Part 3: Personal info --}}
<div class="mb-4 mt-8">
    <h3 class="text-xl font-normal">Persoonsgegevens</h3>
    <p>Dan hebben we nog wat persoonsgegevens en je adres nodig, voor onze ledenadministratie.</p>
</div>
{!! form_until($form, 'country') !!}

{{-- Part 4: Referrer --}}
<div class="mb-4 mt-8">
    <h3 class="text-xl font-normal">How did you get here?</h3>
    <p>Om een beetje een beeld te krijgen van hoe goed (of slecht) onze marketing is, kan je aangeven hoe je bij Gumbo terecht bent gekomen?</p>
</div>
{!! form_until($form, 'referrer') !!}

{{-- Part 5: Last checks --}}
<div class="mb-4 mt-8">
    <h3 class="text-xl font-normal">Aller-, aller-, állerlaatste vraagjes...</h3>
    <p>Nog een paar keer klikken en you're done!</p>
</div>
{!! form_rest($form) !!}

{{-- Done :) --}}
{!! form_end($form) !!}
