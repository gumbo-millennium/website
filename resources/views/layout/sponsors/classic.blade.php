<div class="sponsor">
    <div class="container sponsor__container">
        <a href="{{ route('sponsors.link', compact('sponsor')) }}" class="sponsor__simple-link">
            <img src="{{ $sponsor->logo_color_url }}" alt="{{ $sponsor->title }}" class="sponsor__simple-logo">
        </a>
    </div>
</div>
