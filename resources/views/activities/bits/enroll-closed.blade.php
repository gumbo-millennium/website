{{--
    Enrollments closed, user cannot sign up
    Show if they're too early or too late too.
--}}

<p>
    <button class="btn btn-brand" type="button" disabled="disabled">Inschrijven</button><br />
    <span class="text-red-700">{{
        $activity->enrollment_start > now()
        ? 'Je kan je niet meer inschrijven'
        : 'Je kan je nog niet inschrijven'
    }}</span>
</p>
