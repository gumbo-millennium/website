<div class="shop-header">
    <img role="none" class="shop-header-image" src="{{ $product->valid_image->width(1024)->jpg() }}" />

    <div class="shop-header-gradient"></div>

    <div class="shop-header-body-wrapper">
        <div class="container shop-header-body p-4 md:p-8 md:pb-12 lg:pb-16 flex flex-col md:flex-row items-stretch">
            <div class="flex flex-col flex-grow md:justify-end">
                <p class="font-medium text-white upppercase text-xl md:text-xl lg:text-2xl md:mb-2">
                    Nieuw in de shop
                </p>

                <h3 class="shop-header-title font-title font-bold text-brand-500 text-5xl lg:text-8xl">
                    {{ $product->default_variant->display_name }}
                </h3>
            </div>

            <div class="md:flex items-end">
                <a href="{{ $product->default_variant->url }}" class="btn btn--small btn--brand">Nu bekijken</a>
            </div>

        </div>
    </div>
</div>
