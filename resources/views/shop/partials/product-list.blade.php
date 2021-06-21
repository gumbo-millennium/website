@php($readonly ??= true)

<ul class="grid grid-cols-1 gap-4 {{ $readonly ?: 'md:grid-cols-2 lg:grid-cols-3' }}">
    @foreach ($cartItems as $item)
    <li>
        <div class="flex bg-gray-50 rounded-lg items-center space-x-4 p-2">
            <img
                class="flex-shrink-0 rounded-sm h-16 w-16 object-cover"
                src="{{ $item->associatedModel->valid_image_url }}"
                alt="Afbeelding van {{ $item->name }}"
            />

            <div class="flex-grow">
                @if ($readonly)
                <p class="font-medium text-lg truncate">
                    {{ $item->name }}
                </p>
                @else
                <p class="font-medium text-lg truncate">
                    <a href="{{ $item->associatedModel->url }}">{{ $item->name }}</a>
                </p>
                @endif

                <p class="text-lg font-title">{{ Str::price($item->price) }}</p>
            </div>

            @if ($readonly)
            <data class="text-center select-none">
                {{ $item->quantity }} stuks
            </data>
            @else
            <form method="POST" action="{{ route('shop.cart.update') }}" class="grid grid-cols-3 gap-2">
                @method('PATCH')
                @csrf
                <input type="hidden" name="id" value="{{ $item->id }}" />

                <button
                    name="quantity"
                    value="{{ max($item->quantity - 1, 0) }}"
                    class="flex items-center justify-center appearance-none rounded-full h-6 w-6 shadow">
                    @svg('solid/minus', 'h-2')
                </button>

                <data class="text-center select-none">
                    {{ $item->quantity }}
                </data>

                @if ($item->quantity >= 5)
                    <div role="presentation"
                        class="flex items-center justify-center appearance-none rounded-full h-6 w-6 shadow bg-gray-100 text-gray-500 cursor-not-allowed">
                        @svg('solid/plus', 'h-2')
                    </div>
                @else
                    <button name="quantity" value="{{ min($item->quantity + 1, 5) }}"
                        class="flex items-center justify-center appearance-none rounded-full h-6 w-6 shadow bg-brand-primary-1 text-white">
                        @svg('solid/plus', 'h-2')
                    </button>
                @endif
            </form>
            @endif
        </div>
    </li>
    @endforeach
</ul>

<div class="grid grid-cols-1 {{ $readonly ?: 'md:grid-cols-2' }} gap-4 mt-8">
    <div class="p-2 rounded-lg bg-gray-50 grid grid-cols-2">
        <p>Transactiekosten</p>
        <p class="text-right font-title text-lg">{{ Str::price(Cart::getTotal() - Cart::getSubTotal()) }}</p>
    </div>

    <div class="p-2 rounded-lg bg-gray-50 grid grid-cols-2">
        <p class="font-bold">Totaal</p>
        <p class="text-right font-title text-lg text-brand-primary-3">{{ Str::price(Cart::getTotal()) }}</p>
    </div>
</div>
