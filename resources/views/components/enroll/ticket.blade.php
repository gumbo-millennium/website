<div class="flex flex-col rounded-lg shadow overflow-hidden" data-ticket="{{ $ticket->id }}">
    <div class="px-6 py-8 bg-white md:p-10 md:pb-6 flex-grow">
        <div class="flex items-center gap-x-4">
            <h3 class="inline-flex px-4 py-1 rounded-full text-sm font-semibold tracking-wide uppercase bg-indigo-100 text-indigo-600"
                id="ticket-{{ $ticket->id }}">
                {{ $ticket->title }}
            </h3>

            @if ($ticket->members_only)
            <span class="flex items-center" title="@lang('Members Only')">
                <x-icon icon="solid/lock" class="h-4 text-gray-400 mr-2" />
                <span class="sr-only">
                    @lang('Members Only')
                </span>
            </span>
            @endif
        </div>

        <div class="mt-4 flex items-baseline text-6xl font-extrabold">
            {{ Str::price($ticket->total_price) ?? __('Free') }}
        </div>

        @if ($ticket->description)
        <p class="mt-5 text-lg text-gray-500">
            {{ $ticket->description }}
        </p>
        @endif
    </div>
    <div class="flex-1 flex flex-col justify-between px-6 pt-6 pb-8 bg-gray-50 space-y-6 md:p-10 md:pt-6">
        <ul role="list" class="space-y-4">
            <li class="flex items-start">
                <div class="flex-shrink-0">
                    @if ($ticket->members_only)
                    <x-icon icon="solid/user-friends" class="h-6 w-6 text-green-600" />
                    @else
                    <x-icon icon="solid/globe-europe" class="h-6 w-6 text-green-600" />
                    @endif
                </div>
                <p class="ml-3 text-base text-gray-700">
                    @if ($ticket->members_only)
                    @lang('Members Only')
                    @else
                    @lang('Public')
                    @endif
                </p>
            </li>

            <li class="flex items-start">
                <div class="flex-shrink-0">
                    @if ($ticket->quantity_available === 0)
                    <x-icon icon="solid/ticket-alt" class="h-6 w-6 text-red-600" />
                    @else
                    <x-icon icon="solid/ticket-alt" class="h-6 w-6 text-green-600" />
                    @endif
                </div>
                <p class="ml-3 text-base text-gray-700">
                    @if ($ticket->quantity === null)
                    @lang('No ticket limit')
                    @elseif ($ticket->quantity_available === 0)
                    @lang('Sold Out')
                    @else
                    @lang(':quantity tickets, :available left', [
                        'quantity' => $ticket->quantity,
                        'available' => $ticket->quantity_available,
                    ])
                    @endif
                </p>
            </li>

            <li class="flex items-start">
                <div class="flex-shrink-0">
                    @if ($ticket->is_being_sold)
                    <x-icon icon="solid/clock" class="h-6 w-6 text-green-600" />
                    @else
                    <x-icon icon="solid/clock" class="h-6 w-6 text-red-600" />
                    @endif
                </div>
                <p class="ml-3 text-base text-gray-700">
                    {{ $ticket->available_range }}
                </p>
            </li>

            <div class="rounded-md shadow">
                @if ($ticket->isAvailableFor($user))
                <button name="ticket_id" value="{{ $ticket->id }}" class="btn btn--brand btn--small my-0 w-full"
                    data-test-action="buy-{{ $ticket->id }}"
                    aria-describedby="ticket-{{ $ticket->id }}">
                    @lang('Enroll')
                </button>
                @else
                <div class="relative" role="none">
                    <button type="button" disabled class="btn btn--small my-0 w-full"
                        data-test-action="show-{{ $ticket->id }}"
                        aria-describedby="ticket-{{ $ticket->id }}">
                        @lang('Not Available')
                    </button>
                    <div class="absolute inset-0 bg-transparent"></div>
                </div>
                @endif
            </div>
    </div>
</div>
