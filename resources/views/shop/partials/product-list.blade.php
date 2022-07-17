@php($readonly ??= true)

<ul class="grid grid-cols-1 gap-4 {{ $readonly ?: 'md:grid-cols-2 lg:grid-cols-3' }}">
    @foreach ($cartItems as $item)
    <li>
        <div class="flex bg-gray-50 rounded-lg items-center space-x-4 p-2">
            <img
                class="flex-shrink-0 rounded-sm h-16 w-16 object-cover"
                src="{{ $item->associatedModel->valid_image->width(128) }}"
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
                    <x-icon icon="solid/minus" class="h-2" />
                </button>

                <data class="text-center select-none">
                    {{ $item->quantity }}
                </data>

                @if ($item->quantity >= $item->associatedModel->applied_order_limit)
                    <div role="presentation"
                        class="flex items-center justify-center appearance-none rounded-full h-6 w-6 shadow bg-gray-100 text-gray-500 cursor-not-allowed">
                        <x-icon icon="solid/plus" class="h-2" />
                    </div>
                @else
                    <button name="quantity" value="{{ min($item->quantity + 1, $item->associatedModel->applied_order_limit) }}"
                        class="flex items-center justify-center appearance-none rounded-full h-6 w-6 shadow bg-brand-500 text-white">
                        <x-icon icon="solid/plus" class="h-2" />
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
        <p class="text-right font-title text-lg text-brand-700">{{ Str::price(Cart::getTotal()) }}</p>
    </div>
</div>
