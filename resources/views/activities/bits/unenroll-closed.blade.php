{{--
    Users can't unenroll after the enrollments have closed.
    Hosts can, of coure, still unenroll users
--}}

<p>Je bent <strong>ingeschreven</strong> voor {{ $activity->name }}.</p>
<p><a href="{{ route('enroll.show', compact('activity')) }}">Inschrijving beheren</a></p>
