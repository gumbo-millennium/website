<div class="flex bg-gray-50 rounded-lg items-center p-2">
    <img class="flex-shrink-0 rounded-sm h-16 w-16 object-cover mr-4"
        src="{{ $order->variants->first()->valid_image_url }}" alt="Afbeelding van {{ $order->variants->first()->display_name }}" />

    <div class="flex items-start flex-grow">
        <div class="flex-grow">
            <p class="text-lg font-title">
                <a href="{{ route('shop.order.show', $order) }}">{{ $order->number }}</a>
            </p>

            <p class="text-sm truncate">
                Geplaatst op:
                <time datetime="{{ $order->created_at->format('Y-m-d') }}">
                    {{ $order->created_at->isoFormat('DD MMMM YYYY') }}
                </time>
            </p>
        </div>

        <div>
            @if ($order->cancelled_at)
                <p class="text-center uppercase rounded-full select-none text-sm py-1 px-2 border border-red-400 text-red-500">
                    Geannuleerd
                </p>
            @elseif ($order->payment_id === null)
                <p class="text-center rounded-full select-none text-sm py-1 px-2 bg-black text-white">
                    limbo
                </p>
            @else
                <p class="text-center rounded-full select-none text-sm py-1 px-2 border border-gray-400">
                    {{ Str::price($order->price) }}
                </p>
            @endif
        </div>
    </div>
</div>
