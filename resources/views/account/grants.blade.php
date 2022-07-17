@extends('layout.variants.basic')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Jouw <strong>toestemmingen</strong></h1>
<p class="text-lg text-gray-600 mb-4">Jouw privacy gaat voor, tenzij je dat liever niet hebt.</p>

<a href="{{ route('account.index') }}" class="w-full block mb-4">« Terug naar overzicht</a>

<p class="mb-2">
    Hieronder zie je alle toestemmingen die je hebt gegeven. Je kan deze altijd bijwerken als je wilt.
</p>

<p>
    <strong>Let op:</strong>
    Het kan zijn dat het even duurt voordat je toestemmingen overal zichtbaar zijn, sommige pagina's worden
    een tijdje lokaal opgeslagen.
</p>
<p>
    Als je wijzigingen na 24 uur nog steeds niet zijn doorgevoerd, laat het dan weten!
</p>

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}

@endsection
