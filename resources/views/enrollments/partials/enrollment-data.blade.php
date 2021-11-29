@php($fee = optional($enrollment->payments->sortBy('created_at'))->price ?? ($enrollment->ticket->total_price - $enrollment->ticket->price))
<div class="grid grid-cols-1">
    <h3 class="font-title text-3xl font-bold mb-4">Jouw gegevens</h3>
    <dl class="enroll-list">
        <dt>Naam</dt>
        <dd>{{ $enrollment->user->name }}</dd>

        <dt>E-mailadres</dt>
        <dd>{{ $enrollment->user->email }}</dd>
    </dl>

    <h3 class="font-title text-3xl font-bold mb-4 mt-8">Jouw inschrijving</h3>
    <dl class="enroll-list">
        <dt>Activiteit</dt>
        <dd>{{ $enrollment->activity->name }}</dd>

        <dt>Aanvang activiteit</dt>
        <dd>{{ $enrollment->activity->start_date->isoFormat('ddd DD MMMM YYYY, HH:mm') }}</dd>

        <dt>Ticket</dt>
        <dd>{{ $enrollment->ticket->title }}</dd>

        <dt>Ticketprijs</dt>
        @if ($enrollment->ticket->total_price > 0)
            <dd>{{ Str::price($enrollment->ticket->total_price) }}</dd>
            <dd class="text-sm">(incl. {{ Str::price($fee) }} transactiekosten)</dd>
        @else
            <dd>Gratis</dd>
        @endif
    </dl>

    @if ($enrollment->form)
    <h3 class="font-title text-3xl font-bold mb-4 mt-8">Ingevulde gegevens</h3>
    <dl class="enroll-list">
        @foreach ($enrollment->form as $name => $value)
        <dt>{{ $name }}</dt>
        <dd>{{ $value ?: '—' }}</dd>
        @endforeach
    </dl>
    @endif
</div>
