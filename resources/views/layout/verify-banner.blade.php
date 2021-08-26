<div class="bg-brand-primary-2 font-normal text-light">
    <form class="container py-1 flex flex-row items-center" method="POST" action="{{ route('verification.resend') }}">
        @csrf

        {{-- Icon --}}
        @svg('solid/exclamation-triangle', 'h-4 mr-4 my-1')

        {{-- Title --}}
        <h3 class="mr-4 flex-grow my-1">
            <span class="hidden md:block">Je moet je e-mailadres nog bevestigen!</span>
            <span class="block md:hidden">Bevestig je e-mailadres</span>
        </h3>

        {{-- Link --}}
        <button type="submit"
            title="Opnieuw sturen"
            class="appearance-none no-underline bg-brand-primary-3 px-4 py-1 rounded text-light hover:text-light hover:bg-brand-secondary-3 border border-light flex flex-row items-center">
            @svg('solid/redo', ['class' => 'icon h-4 md:mr-2 my-1', 'role' => 'presentation', 'aria-label' => 'Opnieuw sturen'])
            <span class="hidden md:block">
                Opnieuw sturen
            </span>
        </a>
    </form>
</div>
