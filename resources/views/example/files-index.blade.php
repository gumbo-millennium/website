@php
$searchTopics = [
['line-graph', 'Statistics'],
['calendar-alt', 'Calendar Events'],
['comments', 'Conversations'],
['envelope-open', 'Messages'],
['tags', 'Pricing'],
['pencil-ruler', 'Account Settings'],
['wallet', 'Payments'],
['key', 'Security']
];
@endphp

{{-- Page header --}}
<div class="support-hero support-header">
    <form class="container support-header__container">
        {{-- Search field --}}
        <div class="support-header__search">
            <i class="fas fa-search support-header__search-icon"></i>
            <input type="search" class="support-header__search-field" placeholder="Search help topics">
        </div>

        {{-- Common topics (x4) --}}
        @if (!empty($searchTopics))
        <div class="support-header__topic-list">
            @foreach ($searchTopics as list($searchIcon, $searchTopic))
            <a href="#find-by={{ $searchTopic }}" class="support-header__topic">
                <i class="support-header__topic-icon fas {{ $searchIcon }}"></i>
                <span class="support-header__topic-name">{{ $searchTopic}}</span>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>


<!-- Page Header-->
<section class="module-header parallax bg-dark bg-gradient" data-background="assets/images/module-30.jpg">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="h3 m-b-40">How can we help?</h1>
                <div class="hero-search">
                    <form>
                        <input class="form-control form-round" type="search" placeholder="Search">
                        <button class="search-button" type="submit"><span class="fa fa-search"></span></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Page Header end-->

<!-- Iconbox-->
<section class="module">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="special-heading">
                    <h4>Popular Help Topics</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="support-box">
                    <div class="support-box-icon"><span class="icon icon-basic-compass"></span></div>
                    <div class="support-box-title">
                        <h6>Getting Started</h6>
                    </div>
                    <div class="support-box-content">
                        <p>Map where your photos were taken and discover local points of interest. Map where your photos.</p>
                    </div>
                    <div class="support-box-link"><a href="support-single.html"></a></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="support-box">
                    <div class="support-box-icon"><span class="icon icon-basic-tablet"></span></div>
                    <div class="support-box-title">
                        <h6>Mobile Apps</h6>
                    </div>
                    <div class="support-box-content">
                        <p>Map where your photos were taken and discover local points of interest. Map where your photos.</p>
                    </div>
                    <div class="support-box-link"><a href="support-single.html"></a></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="support-box">
                    <div class="support-box-icon"><span class="icon icon-basic-lightbulb"></span></div>
                    <div class="support-box-title">
                        <h6>Components</h6>
                    </div>
                    <div class="support-box-content">
                        <p>Map where your photos were taken and discover local points of interest. Map where your photos.</p>
                    </div>
                    <div class="support-box-link"><a href="support-single.html"></a></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="support-box">
                    <div class="support-box-icon"><span class="icon icon-basic-gear"></span></div>
                    <div class="support-box-title">
                        <h6>Account Settings</h6>
                    </div>
                    <div class="support-box-content">
                        <p>Map where your photos were taken and discover local points of interest. Map where your photos.</p>
                    </div>
                    <div class="support-box-link"><a href="support-single.html"></a></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="support-box">
                    <div class="support-box-icon"><span class="icon icon-basic-star"></span></div>
                    <div class="support-box-title">
                        <h6>Shop Owners</h6>
                    </div>
                    <div class="support-box-content">
                        <p>Map where your photos were taken and discover local points of interest. Map where your photos.</p>
                    </div>
                    <div class="support-box-link"><a href="support-single.html"></a></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="support-box">
                    <div class="support-box-icon"><span class="icon icon-basic-share"></span></div>
                    <div class="support-box-title">
                        <h6>JavaScript</h6>
                    </div>
                    <div class="support-box-content">
                        <p>Map where your photos were taken and discover local points of interest. Map where your photos.</p>
                    </div>
                    <div class="support-box-link"><a href="support-single.html"></a></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!---->
<section class="module p-t-0">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="special-heading">
                    <h4>Promoted articles</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <ul class="arrow-list">
                    <li><a href="#">How to lorem ipsum?</a></li>
                    <li><a href="#">How to curabitur malesuada hendrerit?</a></li>
                    <li><a href="#">Can I gravida quis diam ac euismod?</a></li>
                    <li><a href="#">What is consectetur aliquam tortor?</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="arrow-list">
                    <li><a href="#">How to lorem ipsum?</a></li>
                    <li><a href="#">How to curabitur malesuada hendrerit?</a></li>
                    <li><a href="#">Can I gravida quis diam ac euismod?</a></li>
                    <li><a href="#">What is consectetur aliquam tortor?</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <ul class="arrow-list">
                    <li><a href="#">How to lorem ipsum?</a></li>
                    <li><a href="#">How to curabitur malesuada hendrerit?</a></li>
                    <li><a href="#">Can I gravida quis diam ac euismod?</a></li>
                    <li><a href="#">What is consectetur aliquam tortor?</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>
<!---->
