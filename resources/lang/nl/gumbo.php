<?php

use App\Models\ActivityMessage;

return [
    'target-audiences' => [
        ActivityMessage::AUDIENCE_ANY => 'Bevestigde inschrijvingen',
        ActivityMessage::AUDIENCE_CONFIRMED => 'Niet-bevestigde inschrijvingen',
        ActivityMessage::AUDIENCE_PENDING => 'Zowel bevestigde als niet-bevestigde inschrijvingen',
    ],
];
