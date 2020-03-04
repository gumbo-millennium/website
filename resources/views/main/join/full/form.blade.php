@php
$userFields = [
    [
        'type' => 'text',
        'name' => 'first_name',
        'label' => 'Voornaam',
        'placeholder' => 'Patrick',
        'value' => optional($user)->first_name ?? null
    ],
    [
        'type' => 'text',
        'name' => 'insert',
        'label' => 'Tussenvoegsel',
        'value' => optional($user)->insert ?? null
    ],
    [
        'type' => 'text',
        'name' => 'last_name',
        'label' => 'Achternaam',
        'placeholder' => 'Hernandez',
        'value' => optional($user)->last_name ?? null
    ],
    [
        'type' => 'email',
        'name' => 'email',
        'label' => 'E-mail adres',
        'help' => 'Alle correspondentie zal naar dit adres gaan, inclusief je account wachtwoord',
        'placeholder' => 'born-to-be-alive@villagepeople.com',
        'value' => optional($user)->email ?? null
    ],
];
$personalFields = [
    ['select', 'gender', 'Geslacht', null, 'man, vrouw, gevechtshelikopter, …', 'options' => [
        'man' => 'Man',
        'vrouw' => 'Vrouw'
    ]],
    ['text', 'date_of_birth', 'Geboortedatum', null, '01-01-1991'],
    ['checkbox', 'windesheim_student', 'Ik studeer op Windesheim', 'Of je op Windesheim studeert bepaald het type lidmaatschap dat je kan krijgen'],
];
$addressFieds = [
    ['text', 'street', 'Straatnaam', null, 'Campus'],
    ['text', 'number', 'Huisnummer', 'Inclusief toevoegsel, indien van toepassing', '2-6'],
    ['text', 'postal_code', 'Postcode', null, '8017 CA'],
    ['text', 'city', 'Plaats', null, 'Zwolle'],
    ['text', 'phone', 'Telefoonnummer', 'Woon je buiten Nederland? Zet je landcode er dan voor!', '038 11 22 333'],
];
@endphp

{{-- User data header --}}
<div class="my-3">
    <h3>Persoonsgegevens</h3>
</div>

@if(auth()->user() && !auth()->user()->hasRole('member'))
{{-- Display current user --}}
<div class="my-2">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-horizontal align-items-center flex-nowrap">
                <i class="mr-4 fas fa-fw fa-2x fa-user" aria-label="User icon"></i>
                <p class="mr-auto mb-0">
                    <strong class="d-block mb-1">Je bent ingelogd als {{ $user->name }}.</strong>
                    De gegevens van je account zijn alvast hieronder ingevuld.
                </p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Display user form --}}
@each('components.form-field', $userFields, 'field')

{{-- User membership header --}}
<div class="my-3">
    <h3>Over je lidmaatschap</h3>
</div>

{{-- Display personal form --}}
@each('components.form-field', $personalFields, 'field')

{{-- Contact data --}}
<div class="my-1">
    <h3>Contactgegevens</h3>
</div>

{{-- Start of regular form --}}
@each('components.form-field', $addressFieds, 'field')
