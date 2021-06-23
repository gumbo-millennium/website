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
            <p class="text-center rounded-full select-none text-sm py-1 px-2 border border-gray-400">
                {{ Str::price($order->price) }}
            </p>
        </div>
    </div>
</div>
