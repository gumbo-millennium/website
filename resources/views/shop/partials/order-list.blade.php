<ul class="grid grid-cols-1 gap-4">
    @foreach ($order->variants as $item)
    <li>
        <div class="flex bg-gray-50 rounded-lg items-center space-x-4 p-2">
            <img
                class="flex-shrink-0 rounded-sm h-16 w-16 object-cover"
                src="{{ $item->valid_image_url }}"
                alt="Afbeelding van {{ $item->display_name }}"
            />

            <div class="flex-grow">
                <p class="font-medium text-lg truncate">
                    {{ $item->display_name }}
                </p>

                <p class="text-lg font-title">{{ Str::price($item->pivot->price) }}</p>
            </div>

            <data class="text-center select-none">
                {{ $item->pivot->quantity }} stuks
            </data>
        </div>
    </li>
    @endforeach
</ul>

<div class="grid grid-cols-1 gap-4 mt-8">
    <div class="p-2 rounded-lg bg-gray-50 grid grid-cols-2">
        <p>Transactiekosten</p>
        <p class="text-right font-title text-lg">{{ Str::price($order->fee) }}</p>
    </div>

    <div class="p-2 rounded-lg bg-gray-50 grid grid-cols-2">
        <p class="font-bold">Totaal</p>
        <p class="text-right font-title text-lg text-brand-primary-3">{{ Str::price($order->price) }}</p>
    </div>
</div>
