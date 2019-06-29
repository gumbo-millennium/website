@php
$user = auth()->user() ?? null;
$userFields = [
    [
        'type' => 'text',
        'name' => 'name',
        'label' => 'Naam',
        'placeholder' => 'Patrick Hernandez',
        'value' => optional($user)->name ?? null
    ],
    [
        'type' => 'email',
        'name' => 'email',
        'label' => 'E-mail adres',
        'help' => 'Dit hoeft niet je opleidings e-mailadres te zijn.',
        'placeholder' => 'born-to-be-alive@villagepeople.com',
        'value' => optional($user)->email ?? null
    ],
    [
        'type' => 'text',
        'name' => 'phone',
        'label' => 'Telefoonnummer',
        'help' => 'Woon je buiten Nederland? Zet je landcode er dan voor!',
        'placeholder' => '038 11 22 333'
    ],
];
@endphp

@if(auth()->user() && !auth()->user()->hasRole('member'))
{{-- Display current user --}}
<div class="my-2">
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-horizontal align-items-center flex-nowrap">
                <i class="mr-4 fas fa-fw fa-2x fa-user" aria-label="User icon"></i>
                <p class="mr-auto mb-0">
                    <strong class="d-block mb-1">Je bent ingelogd als {{ $user->alias ?? $user->first_name }}.</strong>
                    De gegevens van je account zijn alvast hieronder ingevuld.
                </p>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Display user form --}}
@each('components.form-field', $userFields, 'field')
