<footer class="footer container">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
        <div class="hidden xl:block">&nbsp;</div>

        <div class="text-center md:text-left xl:text-center">
            <p class="md:flex flex-col items-start xl:block">
                <span>© Gumbo Millennium {{ today()->year }}.</span>
                <span>Alle rechten voorbehouden.</span>
            </p>
        </div>

        <div class="hidden md:flex items-center justify-end text-gray-300">
            <x-icon icon="solid/leaf" class="h-4 mr-2" />

            <a class="no-underline text-gray-300 hover:text-gray-700 hover:underline focus:text-gray-700 focus:underline" rel="nofollow noopener" target="_blank" href="https://www.scaleway.com/en/environmental-leadership/">
                Onze website is CO<sub>2</sub> neutraal gehost.
            </a>
        </div>
    </div>
</footer>
