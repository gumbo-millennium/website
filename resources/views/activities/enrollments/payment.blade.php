@extends('layout.main')

@section('title', "Inschrijving betalen - {$activity->name} - Gumbo Millennium")

@section('content')
<div class="header">
    <div class="container header__container activity-header">
        <h1 class="header__title header__title--single">Inschrijving afrekenen</h1>
    </div>
    <div class="header__floating" role="presentation">
        {{ Str::ascii($activity->name) }}
    </div>
</div>

<div class="container-md">
    <div class="flex flex-row">
        <div class="flex-grow">
            <p>
                Om je inschrijving voor {{ $activity->title }} af te ronden, dien je {{ Str::price( $enrollment->total_price ) }} te betalen.
            </p>
            <p>
                Al onze betalingen lopen via iDEAL. Wil je niet betalen via iDEAL of wil je een betalingsregeling treffen, neem dan contact op met het bestuur.
            </p>
        </div>
        <div class="flex-shrink-0 w-3/12">
            <div class="card">
                Overzicht van je bestelling

                <table class="w-full">
                    <tbody>
                        @foreach ($invoiceLines as list($price, $label))
                        <tr class="my-2">
                            <td>{{ $label }}</td>
                            <td class="text-right">{{ Str::price($price, true) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if ($invoiceCoupon)
                        <tr>
                            <td>{{ $invoiceCoupon->get('label') }}</td>
                            <td class="text-right mt-2 pt-2 border-t border-black">{{ Str::price($invoiceCoupon->get('discount') * -1) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>&nbsp;</td>
                            <td class="text-right font-bold mt-2 pt-2 border-t border-black">{{ Str::price($enrollment->total_price) }}</td>
                        </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('enroll.pay', compact('activity')) }}" method="post">
@csrf
<label for="bank">Bank</label>
<select name="bank" id="bank">
@foreach ($banks as $bank => $bankName)
<option value="{{ $bank }}">{{ $bankName }}</option>
@endforeach
</select>

<label>
    <input type="checkbox" name="accept" required />
    Ik ga akkoord met de voorwaarden voor betalingen via de Gumbo Website.
</label>

<input type="submit" value="Ok">
</form>

@endsection
