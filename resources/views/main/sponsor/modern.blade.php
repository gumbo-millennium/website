{{-- Deluxe sponsor blok --}}
<div class="donor-large" style="background-image: url({{ asset($sponsor->image_url) }})">
    <div class="container donor-large__container">
        <div class="donor-large__backdrop" style="background-image: url({{ asset($sponsor->image_url) }})">
        </div>
        <div class="donor-large__info">
            <img src="{{ asset($sponsor->logo_url) }}" alt="Logo {{ $sponsor->name }}" class="donor-large__logo">
            <p class="donor-large__text">
                {{ $sponsor->description }}
            </p>

            <a href="{{ $sponsor->url }}" class="donor-large__btn" target="__blank" referrerpolicy="origin" rel="external nofollow noopener">
                {{ $sponsor->action ?? 'Lees meer' }}
            </a>
        </div>
    </div>
</div>
