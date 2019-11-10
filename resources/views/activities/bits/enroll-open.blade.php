{{--
    User can enroll for this activity
    Show a message if they need to pay for the activity
--}}

<form action="{{ route('enroll.create', ['activity' => $activity]) }}" method="post">
    @csrf
    <button class="btn btn-brand" style="submit">Inschrijven</button>
    @if ($is_paid)
    <p class="text-gray-600">Voor deze inschrijving is betaling vereist.</p>
    @endif
</form>
