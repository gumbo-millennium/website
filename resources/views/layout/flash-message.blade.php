{{-- Flashed messages --}}
@if (flash()->message)
<div class="container mt-2" role="alert">
    <div class="notice {{ flash()->class }}">
        <p>{{ flash()->message }}</p>
    </div>
</div>
@endif
