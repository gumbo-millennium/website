@extends('layout.variants.two-col')

@section('title', "Inschrijving betalen - {$activity->name} - Gumbo Millennium")

{{-- Set sidebar --}}
@section('two-col.right')
@component('activities.bits.sidebar', compact('activity'))
    @slot('showTagline', false)

    @slot('details')
    {{-- Make some room --}}
    <hr class="border-gray-300 my-8" />

    {{-- Price title --}}
    <h2 class="font-title text-xl font-normal my-4">Jouw inschrijving</h2>

    {{-- Price table --}}
    <table class="w-full">
        <tbody>
            {{-- Product lines --}}
            @foreach ($invoiceLines as list($price, $label))
            <tr class="mb-2">
                <td class="text-gray-600">{{ $label }}</td>
                <td class="text-right">{{ Str::price($price, true) }}</td>
            </tr>
            @endforeach

            {{-- Discount line, if any --}}
            @if ($invoiceCoupon)
            <tr class="mb-2">
                <td class="text-gray-600">{{ $invoiceCoupon->get('label') }}</td>
                <td class="text-right">{{ Str::price($invoiceCoupon->get('discount') * -1) }}</td>
            </tr>
            @endif
        </tbody>

        {{-- Total price --}}
        <tfoot>
            <tr class="text-left font-bold">
                <td class="pt-4 text-gray-600">Totaalprijs</td>
                <td class="pt-4 text-right text-brand-600">{{ Str::price($enrollment->total_price) }}</td>
            </tr>
        </tfoot>
    </table>
    @endslot
@endcomponent
@endsection

{{-- Set main --}}
@section('two-col.left')
<h1 class="text-3xl font-title mb-4">Inschrijving afrekenen</h1>

<div class="leading-loose">
    <p class="mb-4">
        Om je inschrijving voor {{ $activity->title }} af te ronden, dien je het inschrijftarief van {{ Str::price( $enrollment->total_price ) }} te betalen.
    </p>
    <p>
        Al onze betalingen lopen via iDEAL. Wil je niet betalen via iDEAL of wil je een betalingsregeling treffen, neem dan contact op met het bestuur.
    </p>
</div>

{{-- Render form --}}
{!! form($form, ['class' => 'form']) !!}
@endsection
