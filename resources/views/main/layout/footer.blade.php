@inject('menuHelper', 'App\Services\MenuProvider')

{{-- Footer --}}
<footer class="footer" id="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 footer__column">
                <div class="footer__title">
                    Over Gumbo Millennium
                </div>
                <ul class="footer__menu">
                    <li>
                        <a href="#">Home Pages</a>
                    </li>
                    <li>
                        <a href="#">Theme Features</a>
                    </li>
                    <li>
                        <a href="#">Services</a>
                    </li>
                    <li>
                        <a href="#">StoreFront</a>
                    </li>
                    <li>
                        <a href="#">Portfolio</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4 footer__column">
                <div class="footer__title">
                    Connect with us
                </div>
                <ul class="footer__menu">
                    <li>
                        <a href="#">
                            <i class="fa fa-instagram"></i>
                            Instagram
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-twitter"></i>
                            Twitter
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-dribbble"></i>
                            Dribbble
                        </a>
                    </li>
                    <li>
                        <a href="#">
                            <i class="fa fa-facebook"></i>
                            Facebook
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4 footer__column footer-newsletter">
                <div class="footer__title">
                    Get awesome updates
                </div>
                <p class="footer-newsletter__caption">
                    Enter your email address for news and product launches in the Awesome Space.
                </p>

                <form class="footer-newsletter__form" autocomplete="off">
                    <input type="email" id="mc-email" class="form-control footer-newsletter__email" placeholder="Email address" required />

                    <button type="submit" class="footer-newsletter__submit">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                    <label for="mc-email" class="text-white footer-newsletter__feedback mt-3"></label>
                </form>
            </div>
        </div>
        <div class="footer__legal">
            <ul class="footer__legal-menu">
                @if ($menuHelper->hasLocation('footer-legal'))
                @foreach ($legalMenuItems = $menuHelper->location('footer-legal') as $menuItem)
                <li>
                    <a href="{{ $menuItem['url'] }}">{{ $menuItem['title'] }}</a>
                </li>
                @endforeach
                @else
                @for ($i = 0; $i < 4; $i++)
                <li>
                    <a href="#">Link {{ pow(2, $i) }}</a>
                </li>
                @endfor
                @endif
            </ul>
        </div>
    </div>
</footer>
<!-- Footer end-->
