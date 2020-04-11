@php
$sponsorClass = Str::slug("sponsor-block--{$sponsor->slug}");
@endphp
@push('main.styles')
<style nonce="{{ csp_nonce() }}">
.{{ $sponsorClass }} {
    background-image: url("{{ $sponsor->backdrop->url('banner') }}");
}
</style>
@endpush
<div class="bg-white sponsor-block {{ $sponsorClass }}">
    <div class="container my-8 text-center">
        <a href="{{ route('sponsors.link', compact('sponsor')) }}">
            <img src="{{ $sponsor->logo_color_url }}" alt="{{ $sponsor->title }}" class="w-64 h-20">
        </a>
    </div>
</div>
