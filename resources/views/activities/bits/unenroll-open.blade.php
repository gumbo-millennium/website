<p>Je bent <strong>ingeschreven</strong> voor {{ $activity->name }}.</p>
<p>
    <a href="{{ route('enroll.show', compact('activity')) }}">Inschrijving beheren</a> –
    <a class="text-red-600" href="{{ route('enroll.delete', compact('activity')) }}">Uitschrijven</a>
</p>
