@php
$userFields = [
    ['text', 'first_name', 'Voornaam', null, 'Patrick'],
    ['text', 'insert', 'Tussenvoegsel', null],
    ['text', 'last_name', 'Achternaam', null, 'Hernandez'],
    ['email', 'email', 'E-mail adres', 'Alle correspondentie zal naar dit adres gaan, inclusief je account wachtwoord', 'born-to-be-alive@villagepeople.com'],
];
$personalFields = [
    ['text', 'date-of-birth', 'Geboortedatum', null, '01-01-1991'],
    ['checkbox', 'windesheim-student', 'Ik studeer op Windesheim', 'Of je op Windesheim studeert bepaald het type lidmaatschap dat je krijgt'],
];
$addressFieds = [
    ['text', 'street', 'Straatnaam', null, 'Campus'],
    ['text', 'number', 'Huisnummer', 'Inclusief toevoegsel, indien van toepassing', '2-6'],
    ['text', 'zipcode', 'Postcode', null, '8017 CA'],
    ['text', 'city', 'Plaats', null, 'Zwolle'],
    ['text', 'phone', 'Telefoonnummer', 'Woon je buiten Nederland? Zet je landcode er dan voor!', '038 11 22 333'],
];
@endphp

{{-- User data header --}}
<div class="my-3">
    <h3>Persoonsgegevens</h3>
</div>

@auth
{{-- Display current user --}}
<div class="row my-2">
    <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2">
        <div class="card">
            <div class="card-body">
                <div class="media">
                    <div class="mr-2 p-2 d-flex align-items-center justify-content-center" style="background-color: ##ced4da; border-radius: 50%; width: 4rem; height: 4rem;">
                        <i class="fas fa-fw fa-user" aria-label="User icon"></i>
                    </div>
                    <div class="media-body">
                        <p>Ingelogd als: <strong>{{ auth()->user()->name }}</strong></p>
                        <p>Je huidige account zal gebruikt worden voor je aanmelding</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else
{{-- Display user form --}}
@each('components.form-field', $userFields, 'field')
@endauth

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
