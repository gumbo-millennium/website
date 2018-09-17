{{-- Meet Gumbo --}}
<div class="central-intro">
    <div class="central-intro__container">
        <h3 class="central-intro__title">
            Maak kennis met Gumbo Millennium
        </h3>
        <hr class="central-intro__divider">
        <p class="central-intro__content">
            Sinds 1991 staat Gumbo Millennium voor gezelligheid en om een fantastische studententijd te beleven.
            <br />
            Met onze eigen plek op Windesheim om te relaxen,
            soosavonden in Studentencafé Het Vliegende Paard
            en veel variërende activiteiten,
            is er voor ieder wat wils binnen Gumbo.
        </p>
    </div>
</div>

{{-- Unique Selling Points --}}
<div class="unique-selling-points">
    <div class="container">
        <header class="unique-selling-points__header">
            <h3 class="unique-selling-points__header-title">Waarom Gumbo Millennium?</h3>
            <p class="unique-selling-points__header-text">
                Een vereniging naast je studie is goed voor je, maar waarom zou je dan voor Gumbo Millennium kiezen?
                We geven je graag een paar goede argumenten!
            </p>
        </header>
        <section class="unique-selling-points__features">
            <div class="row">
            @for ($i = 0; $i < 4; $i++)
                <div class="col-md-6 unique-selling-points__feature">
                    <img src="images/hertog.jpg" class="unique-selling-points__feature-icon" />
                    <section class="unique-selling-points__feature-inner">
                        <h4 class="unique-selling-points__feature-title">
                            Geen ontgroening
                        </h4>
                        <p class="unique-selling-points__feature-desc">
                            Gumbo Millennium doet niet aan ontgroeningen, dus bij ons zul je geen tafarelen uit Feuten
                            zien.
                        </p>
                    </section>
                </div>
            @if ($i == 1)
            </div>
            <div class="row">
            @endif
            @endfor
            </div>
        </section>
    </div>
</div>

{{-- Foto's --}}
<div class="photo-album photo-album--test">
    <div class="container">
        <h2 class="photo-album__title">Gelukkig hebben we de foto's nog</h2>

        <div class="row">
            @for ($i = 1; $i <= 9; $i++)
            <div class="col-lg-4 col-md-6">
                <a href="agency-project.html" class="photo-album-photo">
                    <span class="photo-album-photo__mask">
                        <span class="photo-album-photo__info">
                            <h3 class="photo-album-photo__title">Gumbo lid</h3>
                            <p class="photo-album-photo__text">Ze zijn ook wel bijzonder</p>
                        </span>
                        <span class="photo-album-photo__btn">
                            Bekijk foto
                        </span>
                    </span>
                    <span class="photo-album-photo__photo photo-album-photo__photo--pic-{{ $i }}"></span>
                </a>
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- Sponsor blok --}}
<div class="donor-small">
    <div class="donor-small__container">
        <a href="#" class="donor-small__link">
            <img src="/images/cimsolutions.png" alt="Gumbo Millennium Logo" class="donor-small__image">
        </a>
        {{-- Contains "advertisement" in :after --}}
    </div>
</div>

{{-- Deluxe sponsor blok --}}
<div class="donor-large" style="background-image: url(/images/info-support-bg.jpg)">
    <div class="container donor-large__container">
        <div class="donor-large__backdrop" style="background-image: url(/images/info-support-bg.jpg)">
        </div>
        <div class="donor-large__info">
            <img src="/images/info-support.png" alt="Logo InfoSupport" class="donor-large__logo">
            <p class="donor-large__text">
                “Ook als IT-afstudeerder maak je impact. Bij Info Support werk je aan een relevante afstudeeropdracht die bij jou past. Je
                doet dit samen met mensen die je vooruit helpen. Je krijgt alle ruimte om te leren en nieuwe kennis op te doen. We zien je
                graag bij Info Support!„
            </p>

            <a href="#" class="donor-large__btn">
                Lees meer
            </a>
        </div>
    </div>
</div>

{{-- Testimonials --}}
<div class="testimonials">
    <div class="container">
        <div class="testimonials__quote">
            <span class="testimonials__quote-mark">“</span>
            Alle colleges leiden naar Plaza
        </div>
        <div class="testimonials__meta">
            <img src="images/uifaces/9.jpg" class="testimonials__photo">
            <span class="testimonials__author">
                Roelof Roos
            </span>
        </div>
    </div>
</div>

{{-- Word Lid --}}
<div class="cta-banner">
    <div class="container cta-banner__container">
        <div class="cta-banner__text-container">
            <strong class="cta-banner__text-primary">
                Wordt jij het nieuwste lid van de gezelligste vereniging van Zwolle?
            </strong>
            <p class="cta-banner__text-secondary">
                Meld je dan snel aan, en mis geen dag!
            </p>
        </div>

        <a href="#" class="cta-banner__btn">
            Meld je aan!
        </a>
    </div>
</div>
