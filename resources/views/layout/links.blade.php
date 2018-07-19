{{-- SEO Links --}}
<!-- SEO: TODO -->

{{-- Favicons and app icons --}}
<link rel="shortcut icon" href="assets/images/favicon.png">
<link rel="apple-touch-icon" href="assets/images/apple-touch-icon.png">
<link rel="apple-touch-icon" sizes="72x72" href="assets/images/apple-touch-icon-72x72.png">
<link rel="apple-touch-icon" sizes="114x114" href="assets/images/apple-touch-icon-114x114.png">

{{-- Main stylesheet --}}
<link href="{{ mix('/gumbo.css') }}" rel="stylesheet">

{{-- Web fonts --}}
@if ($page_type ?? null === 'blog')
    {{-- Blog pages have classier fonts, and Open Sans --}}
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif:400,700|Open+Sans:400,700|Zilla+Slab" rel="stylesheet">
@else
    {{-- Other pages just have the normal fonts --}}
    <link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans:400,700" rel="stylesheet">
@endif
