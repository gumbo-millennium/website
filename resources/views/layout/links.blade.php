{{-- SEO Links --}}
<!-- SEO: TODO -->

{{-- Favicons and app icons --}}
<link rel="shortcut icon" href="assets/images/favicon.png">
<link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="72x72" href="assets/images/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="114x114" href="assets/images/apple-touch-icon-114x114.png">

{{-- Main stylesheet --}}
<!-- Website theme -->
<link href="{{ mix('/gumbo.css') }}" rel="stylesheet">

{{-- Web fonts --}}
<link href="https://fonts.googleapis.com/css?family=Hind:400,700%7cLora:400i%7cPoppins:500,600,700" rel="stylesheet">

{{-- Font Awesome (pro or free) --}}
@if (config('app.use-font-awesome-pro'))
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.0.13/css/regular.css" integrity="sha384-HLkkol/uuRVQDnHaAwidOxb1uCbd78FoGV/teF8vONYKRP9oPQcBZKFdi3LYDy/C" crossorigin="anonymous">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.0.13/css/light.css" integrity="sha384-d8NbeymhHpk+ydwT2rk4GxrRuC9pDL/3A6EIedSEYb+LE+KQ5QKgIWTjYwHj/NBs" crossorigin="anonymous">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.0.13/css/fontawesome.css" integrity="sha384-LDuQaX4rOgqi4rbWCyWj3XVBlgDzuxGy/E6vWN6U7c25/eSJIwyKhy9WgZCHQWXz" crossorigin="anonymous">
@else
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/regular.css" integrity="sha384-EWu6DiBz01XlR6XGsVuabDMbDN6RT8cwNoY+3tIH+6pUCfaNldJYJQfQlbEIWLyA" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/fontawesome.css" integrity="sha384-GVa9GOgVQgOk+TNYXu7S/InPTfSDTtBalSgkgqQ7sCik56N9ztlkoTr2f/T44oKV" crossorigin="anonymous">
@endif
