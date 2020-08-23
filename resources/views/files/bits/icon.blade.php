@php
$map = [
    '7z' => 'file-archive',
    'aac' => 'file-audio',
    'csv' => 'file-csv',
    'doc' => 'file-word',
    'docx' => 'file-word',
    'flac' => 'file-audio',
    'gz' => 'file-archive',
    'jpg' => 'file-image',
    'mkv' => 'file-video',
    'mp3' => 'file-audio',
    'mp4' => 'file-video',
    'opus' => 'file-audio',
    'pdf' => 'file-pdf',
    'png' => 'file-image',
    'ppt' => 'file-powerpoint',
    'pptx' => 'file-powerpoint',
    'svg' => 'file-image',
    'xls' => 'file-excel',
    'xlsx' => 'file-excel',
    'zip' => 'file-archive',
];
$extension = Str::afterLast($file->name, '.');
$icon = $map[$extension] ?? 'file';
@endphp
@svg("regular/{$icon}", 'mr-2 flex-none icon block mt-1')
