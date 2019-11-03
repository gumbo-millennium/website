{{--
    Not logged-in users cannot enroll
    The activity.login route registers this event as back link
--}}

<p>Je bent niet ingelogd. Om je aan te melden voor {{ $activity->name }}, moet je eerst inloggen.</p>
<p><a href="{{ route('activity.login', ['activity' => $activity]) }}">Inloggen om aan te melden</a></p>
