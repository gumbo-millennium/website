<?php

return [
    // System name. Used by jobs
    'name' => 'Bestandensysteem',

    // File system titles
    'titles' => [
        'name' => 'Bestanden',
        'index' => 'Categorie overzicht',
        'category' => 'Bestanden in :category',
        'state-desc' => 'Legenda bestandstatus',
    ],

    // File actions
    'actions' => [
        'cancel' => 'Annuleren',
        'view' => 'Bekijk bestand',
        'upload' => 'Upload bestanden',
        'publish' => 'Publiceren',
        'unpublish' => 'Intrekken',
        'edit' => 'Bewerken',
        'delete' => 'Verwijderen',
        'back-to-index' => 'Terug naar categorie overzicht'
    ],

    // Table headers
    'headers' => [
        'title' => 'Bestandsnaam',
        'owner' => 'Geüpload door',
        'state' => 'Status',
        'actions' => 'Acties'
    ],


    // File state lines
    'state' => [
        'pending' => 'In behandeling',
        'checked' => 'Gevalideerd',
        'broken' => 'Beschadigd',
        'pdfa' => 'PDF/A',
        'has-meta' => 'Geïndexeerd',
        'has-thumbnail' => 'Thumbnails',
    ],

    // File state descriptions
    'state-desc' => [
        'pending' => 'Het bestand wordt nagelopen door het systeem.',
        'checked' => 'Het PDF bestand is gecontroleerd als werkend bestand.',
        'broken' => 'Het PDF bestand is beschadigd en kan niet worden opgenomen in de index.',
        'pdfa' => 'Het PDF bestand is geëxporteerd als PDF/A bestand. Dit kan automatisch gedaan zijn.',
        'has-meta' => 'De inhoud van het PDF bestand is opgenomen in de index.',
        'has-thumbnail' => 'het bestand is voorzien van miniaturen.',
    ],

    // messages
    'messages' => [
        'no-categories' => 'Er staan geen categorieën in het systeem.',
        'no-files' => 'Er staan geen bestanden in deze categorie.',
        'broken-files' => 'Deze categorie bevat bestanden die beschadigd zijn. Deze kan je het beste verwijderen.',
        'file-added' => 'Het bestand :file is toegevoegd',
        'file-updated' => 'Het bestand :file is bijgewerkt',
        'file-published' => 'Het bestand :file is gepubliceerd',
        'file-unpublished' => 'Het bestand :file is ingetrokken',
        'file-destroyed' => 'Het bestand :file is verwijderd',

        // PDF/A messages
        'pdfa-started' => 'Het bestand :file zal binnenkort omgezet worden naar PDF/A.',
        'pdfa-already' => 'Het bestand :file is al in PDF/A format.',
    ],

    // Upload dialog
    'upload' => [
        'title' => 'Bestanden uploaden',
        'subtitle' => 'Sleep bestanden hier om ze toe te voegen aan de :category categorie.',
        'queue-hint' => implode(' ', [
            '<strong>Let op:</strong>',
            'De bestanden worden nagelopen op fouten en geoptimaliseerd voor publicatie op het web.',
            'Dit kan een paar minuten duren.'
        ]),
        'rows' => [
            'filename' => 'Bestandsnaam',
            'status' => 'Status',
            'actions' => 'Acties'
        ],
        'close' => 'Sluiten',
        'close-and-reload' => 'Sluiten en herladen'
    ],
];
