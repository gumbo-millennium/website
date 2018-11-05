<?php

return [
    // System name. Used by jobs
    'name' => 'File system',

    // File system titles
    'titles' => [
        'name' => 'File system',
        'index' => 'Category overview',
        'category' => 'Files in :category',
        'state-desc' => 'Explanation of statuses',
    ],

    // File actions
    'actions' => [
        'cancel' => 'Cancel',
        'view' => 'View file',
        'upload' => 'Upload files',
        'publish' => 'Publish',
        'unpublish' => 'Unpublish',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'back-to-index' => 'Back to category list'
    ],

    // Table headers
    'headers' => [
        'title' => 'Filename',
        'owner' => 'Uploaded by',
        'state' => 'Status',
        'actions' => 'Actions'
    ],

    // File state lines
    'state' => [
        'pending' => 'Pending',
        'checked' => 'Checked',
        'broken' => 'Damaged',
        'pdfa' => 'PDF/A',
        'has-meta' => 'Indexed',
        'has-thumbnail' => 'Thumbnails',
    ],

    // File state descriptions
    'state-desc' => [
        'pending' => 'The file is awaiting automatic checks.',
        'checked' => 'The file was validated as a properly readable PDF file',
        'broken' => 'The file is severely damaged and cannot be published.',
        'pdfa' => 'The file has been (re)encoded to the PDF/A format.',
        'has-meta' => 'The file contents have been indexed.',
        'has-thumbnail' => 'The file has received thumbnails.',
    ],

    // messages
    'messages' => [
        'no-categories' => 'There are no categories in the system',
        'no-files' => 'There are no files in this category',
        'broken-files' => 'This category contains broken files, please delete them',
        'file-added' => 'The file :file has been added',
        'file-updated' => 'The file :file has been updated',
        'file-published' => 'The file :file has been published',
        'file-unpublished' => 'The file :file has been unpublished',
        'file-destroyed' => 'The file :file has been deleted',

        // PDF/A messages
        'pdfa-started' => 'The file :file will soon be converted to PDF/A.',
        'pdfa-already' => 'The file :file is already stored as PDF/A.',
    ],

    // Upload dialog
    'upload' => [
        'title' => 'Upload files',
        'subtitle' => 'Drag files here to upload them to the :category category.',
        'queue-hint' => implode(' ', [
            '<strong>Please note:</strong>',
            'The files will be verified and optimised for web delivery.',
            'This might take a couple of minutes.'
        ]),
        'rows' => [
            'filename' => 'Filename',
            'status' => 'Status',
            'actions' => 'Actions'
        ],
        'close' => 'Close',
        'close-and-reload' => 'Close and reload'
    ],
];
