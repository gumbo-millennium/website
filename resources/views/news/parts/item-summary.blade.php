@php
// Uses
use Carbon\Carbon;

// Author
$hasSponsor = (bool) $item->sponsor;
$hasAuthor = !$hasSponsor && $item->author && $item->author->display_name;

// Timestamp
$postTimestamp = $item->published_at ?? $item->created_at;
$postDateIso = $postTimestamp->toIso8601String();
$postDate = $postTimestamp->isoFormat('DD MMMM YYYY, [om] HH:mm');

// Make relative, if asked
if (!empty($relative)) {
    $postDate = $postTimestamp->diffForHumans([
        'syntax' => Carbon::DIFF_RELATIVE_TO_NOW,
        'options' => Carbon::JUST_NOW | Carbon::ONE_DAY_WORDS | Carbon::TWO_DAY_WORDS,
        'parts' => 1
    ]);
}
@endphp

{{-- Optional author --}}
@if ($hasAuthor)
<div class="mr-4 flex flex-row items-center">
    <x-icon icon="solid/user" class="block mr-2" />
    <span>{{ $item->author->display_name }}</span>
</div>
@endif

{{-- Date of publication --}}
<div class="mr-4 flex flex-row items-center">
    <x-icon icon="solid/clock" class="block mr-2" />
    <time datetime="{{ $postDateIso }}">{{ $postDate }}</time>
</div>
