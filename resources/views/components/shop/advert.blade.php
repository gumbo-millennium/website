@props([
  'product',
])
<div class="bg-brand-700 rounded-lg shadow-xl overflow-hidden lg:grid lg:grid-cols-2 lg:gap-4">
  <div class="pt-10 pb-12 px-6 sm:pt-16 sm:px-16 lg:py-16 lg:pr-0 xl:py-20 xl:px-20">
    <div class="lg:self-center">
      <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
        <span class="block">Heb jij je merch al?</span>
        <span class="block">Ook het állernieuwste item?</span>
      </h2>
      <p class="mt-4 text-lg leading-6 text-brand-200">
        Nieuw in de webshop: {{ $product->default_variant?->display_name ?? $product->name }}, en hij kan van jou zijn
        voor maar {{ Str::price($product->price) }}!
      </p>
      <a href="{{ $product->default_variant->url }}"
        class="mt-8 bg-white border border-transparent rounded-md shadow px-5 py-3 inline-flex items-center text-base font-medium text-brand-600 hover:bg-brand-50">
        Nu bekijken
      </a>
    </div>
  </div>
  <div class="-mt-6 aspect-w-5 aspect-h-3 md:aspect-w-2 md:aspect-h-1">
    <img
      class="transform translate-x-6 translate-y-6 rounded-tl-xl object-cover object-left-top sm:translate-x-16 lg:translate-y-20"
      src="{{ $product->valid_image->width(910)->height(470)->fit('crop') }}"
      alt="Foto van {{ $product->default_variant?->display_name ?? $product->name }}" />
  </div>
</div>
