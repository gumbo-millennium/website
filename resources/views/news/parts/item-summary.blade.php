{{-- Optional sponsor --}}
@if ($item->sponsor && !empty($withAdvertorial))
<div class="mr-4 flex flex-row items-center">
    @icon('solid/ad', 'block mr-2')
    <span>Advertorial door {{ $item->sponsor }}</span>
</div>
@endif

{{-- Optional author --}}
@if ($item->author && $item->author->display_name)
<div class="mr-4 flex flex-row items-center">
    @icon('solid/user', 'block mr-2')
    <span>{{ $item->author->display_name }}</span>
</div>
@endif

{{-- Date of publication --}}
@php
$postDate = ($item->created_at ?? $item->published_at)->isoFormat('DD MMMM YYYY, [om] HH:mm');
@endphp
<div class="mr-4 flex flex-row items-center">
    @icon('solid/clock', 'block mr-2')
    <span>{{ $postDate }}</span>
</div>
