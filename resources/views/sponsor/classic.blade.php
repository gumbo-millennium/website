{{-- Sponsor blok --}}
<div class="donor-small">
    <div class="donor-small__container">
        <a href="{{ $sponsor->url }}" target="__blank" referrerpolicy="origin" rel="external nofollow noopener" class="donor-small__link">
            <img src="{{ asset($sponsor->image_url) }}" alt="Logo {{ $sponsor->name }}" class="donor-small__image">
        </a>
    </div>
</div>
