@php
$menuItems = [
    '/' => 'Homepage',
    '/news' => 'Nieuws',
    '/activities' => 'Activiteiten'
];
if ($user && $user->can('viewPublic', App\Models\File::class)) {
    $menuItems['/files'] = 'Documenten';
}
@endphp
<nav class="navbar">
    <div class="container mx-auto flex flex-col md:flex-row flex-nowrap justify-between items-center">
    <div class="flex-grow-0">
        <a class="h-16 my-4" href="/">
            <img src="{{ asset('/svg/logo-text-white.svg') }}" class="h-16" alt="Gumbo Millennium" aria-label="navbar" />
        </a>
    </div>

    <ul class="align-self-end flex flex-col md:flex-row">
        @foreach ($menuItems as $link => $label)
        <li>
            <a href="{{ $link }}" class="block px-6 py-2">{{ $label }}</a>
        </li>
        @endforeach
    </ul>
</div>
</nav>
