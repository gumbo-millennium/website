<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

{{-- Title, meta tags and JSON-LD --}}
{!! SEO::generate(! App::hasDebugModeEnabled()) !!}
{{-- Stylesheet --}}
@vite('resources/css/app.css')

{{-- Google Fonts --}}
<link href="https://fonts.googleapis.com/css?family=Poppins:500,700&display=swap" rel="stylesheet">

{{-- Scripts --}}
@vite('resources/js/app.js')
