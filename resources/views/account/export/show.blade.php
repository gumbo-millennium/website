@extends('layout.variants.basic')

@php($data = [
    'Aangemaakt op' => $export->created_at->isoFormat('LLL'),
    'Afgerond op' => $export->path ? $export->completed_at->isoFormat('LLL') : 'Nog niet afgerond',
    'Verloopt op' => $export->expires_at->isoFormat('LLL'),
])

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl mb-4">Inzageverzoek</h1>

<a href="{{ route('account.export.index') }}" class="w-full block mb-4">« Terug naar overzicht</a>

<p class="mb-2">
    Hieronder zie je de informatie over dit inzageverzoek.
</p>

<p class="text-sm">
    Indien er een download beschikbaar is, kan je deze onderaan de pagina vinden.
</p>

<table class="min-w-full divide-y divide-gray-200 my-4">
    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($data as $key => $value)
        <tr>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm leading-5 text-gray-900">
                    {{ $key }}
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                {{ $value }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Download --}}
<div class="w-full grid grid-cols-1">
    @if ($export->path && !$export->is_expired)
        <a href="{{ route('account.export.download', [$export->id, $export->token]) }}" class="btn btn-small btn-primary text-center">
            @icon('solid/download', 'h-8 mr-4')
            Download
        </a>
    @else
        <button disabled class="btn btn-small text-center" aria-label="Download niet beschikbaar">
            @icon('solid/download', 'h-8 mr-4')
            Download
        </button>
    @endif
</div>
@endsection
