<!-- Page Header-->
<section class="module-header parallax bg-dark bg-dark-30" data-background="assets/images/module-6.jpg">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="h1 m-b-20">Activiteiten</h1>
                <p>Woorden en dingen en bakfietjses.</p>
            </div>
        </div>
    </div>
</section>
<!-- Page Header end-->

<!-- Blog-->
<section class="module">
    <div class="container">
        <div class="row blog-magazine">

            @for ($i = 0; $i < 4; $i++)
            <div class="col-lg-6">
                <!-- Post-->
                <article class="post">
                    <div class="post-background" data-background="http://via.placeholder.com/1100x500">
                        <div class="post-header date-title">
                            <div class="date-block text-bold">
                                <div class="date-number">24</div>
                                <div class="date-month">JAN</div>
                            </div>
                            <div class="date-body align-self-center">
                                <h2 class="post-title"><a href="/event/single">Bluetooth Speaker</a></h2>
                                <ul class="post-meta h5">
                                    <li>November 18, 2016</li>
                                    <li><a href="#">Branding</a>, <a href="#">Design</a></li>
                                    <li><a href="#">3 Comments</a></li>
                                </ul>
                            </div>
                        </div>
                        <a class="post-background-link" href="/event/single"></a>
                    </div>
                </article>
                <!-- Post end-->
            </div>
            @endfor
        </div>
    </div>
</section>
<!-- Blog end-->

<!-- Pagination-->
<section class="module-sm module-gray">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <nav>
                    <ul class="pagination h4">
                        <li class="page-item next"><a class="page-link" href="#"><span class="arrows arrows-arrows-slim-right"></span></a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                        <li class="page-item prev"><a class="page-link" href="#"><span class="arrows arrows-arrows-slim-left"></span></a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</section>
<!-- Pagination end-->
