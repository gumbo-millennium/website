<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Barcode Scanner - Gumbo Millennium</title>

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-900 min-h-screen">
  <div class="h-screen mx-auto max-w-2xl py-4 px-4 flex flex-col">
      @yield('content')
  </div>
</body>
</html>
