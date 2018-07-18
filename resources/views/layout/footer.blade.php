@inject('menuHelper', 'App\Services\MenuProvider')

{{-- Footer --}}
<footer class="footer" id="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="title">
                    Over Gumbo Millennium
                </div>
                <ul class="menu">
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
            <div class="col-md-4">
                <div class="title">
                    Connect with us
                </div>
                <ul class="menu">
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
            <div class="col-md-4 newsletter">
                <div class="title">
                    Get awesome updates
                </div>
                <p>
                    Enter your email address for news and product launches in the Awesome Space.
                </p>

                <form class="newsletter-form" autocomplete="off">
                    <input type="email" id="mc-email" class="form-control" placeholder="Email address" required />

                    <button type="submit">
                        <i class="fa fa-chevron-right"></i>
                    </button>
                    <label for="mc-email" class="text-white newsletter-feedback mt-3"></label>
                </form>
            </div>
        </div>
        @if ($menuHelper->hasLocation('footer-legal'))
        <div class="bottom">
            <ul>
                @foreach ($menuHelper->location('footer-legal') as $menuItem)
                <li><a href="{{ $menuItem['url'] }}">{{ $menuItem['title'] }}</a></li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</footer>
<!-- Footer end-->
