<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

{{-- Title, meta tags and JSON-LD --}}
{!! SEO::generate(! App::hasDebugModeEnabled()) !!}
{{-- Stylesheet --}}
<link rel="stylesheet" href="{{ mix('app.css') }}">

{{-- Google Fonts --}}
<link href="https://fonts.googleapis.com/css?family=Poppins:500,700&display=swap" rel="stylesheet">

{{-- Scripts --}}
<script src="{{ mix('manifest.js') }}" defer></script>
<script src="{{ mix('vendor.js') }}" defer></script>
<script src="{{ mix('app.js') }}" defer></script>
