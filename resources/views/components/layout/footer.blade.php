@props([
  'simple' => false,
])
<footer class="footer container">
    <div class="grid grid-cols-1 {{ $simple ? '' : 'md:grid-cols-2' }}">
        <p class="flex items-center gap-4">
            <span>© Gumbo Millennium {{ today()->year }}</span>
            <a href="/privacy" class="underline hover:no-underline" rel="privacy">Privacybeleid</a>
        </p>

        @unless ($simple)
        <div class="hidden md:flex items-center justify-end text-gray-300">
            <x-icon icon="solid/leaf" class="h-4 mr-2" />

            <a class="no-underline text-gray-300 hover:text-gray-700 hover:underline focus:text-gray-700 focus:underline" rel="nofollow noopener" target="_blank" href="https://www.scaleway.com/en/environmental-leadership/">
                Onze website is CO<sub>2</sub> neutraal gehost.
            </a>
        </div>
        @endunless
    </div>
</footer>
