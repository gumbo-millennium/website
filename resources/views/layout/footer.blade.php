<footer class="footer container">
    <div class="grid grid-cols-1 md:grid-cols-3">
        <div class="hidden md:block">&nbsp;</div>

        <div class="text-center">
            <p>© Gumbo Millennium {{ today()->year }}. Alle rechten voorbehouden.</p>
        </div>

        <div class="hidden md:flex items-center justify-end text-gray-300">
            @icon('solid/leaf', 'h-4 mr-2')

            <a class="no-underline text-gray-300 hover:text-gray-700 hover:underline focus:text-gray-700 focus:underline" rel="nofollow noopener" target="_blank" href="https://www.scaleway.com/en/environmental-leadership/">
                Onze website is CO<sub>2</sub> neutraal gehost.
            </a>
        </div>
    </div>
</footer>
