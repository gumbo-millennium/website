@props(['enrollment'])
<div class="bg-white rounded-lg p-4" x-data="{ show: false }">
    <div x-cloak x-show="show" class="pt-4">
      @if ($enrollment->has2dBarcode())
        <img src="{{ Enroll::getBarcodeImage($enrollment) }}" alt="Barcode" height="80" class="mx-auto max-w-full">
      @else
        <img src="{{ Enroll::getBarcodeImage($enrollment, 400) }}" alt="Barcode" height="200" width="200" class="mx-auto">
      @endif
      <pre class="text-center mt-2 text-wrap max-w-full">{{ $enrollment->barcode }}</pre>
    </div>
    <div class="text-center" x-show="!show">
      <x-button type=button @click="show = true">
        {{ __('Show barcode') }}
      </x-button>
    </div>
</div>
