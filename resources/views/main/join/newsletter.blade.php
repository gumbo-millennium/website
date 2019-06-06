{{-- Privacy policy --}}
<div class="my-1">
    <h3>Aanmelden Gumbode</h3>
</div>

<div class="row">
    <div class="col-sm-2">
        <label for="newsletter_accept">Nieuwsbrief</label>
    </div>
    <div class="col-sm-10">
        <div class="custom-control custom-checkbox mb-2">
            <input
                type="checkbox"
                class="custom-control-input"
                name="newsletter_accept"
                id="newsletter_accept"
                {{ old('newsletter_accept') ? 'checked' : '' }} />
            <label class="custom-control-label" for="newsletter_accept">
                Aanmelden voor de Gumbode (ongeveer 1x per maand)
            </label>
        </div>
        <p>
            Ongeveer één keer maand sturen wij al onze leden een nieuwsbrief (de Gumbode), met daarin een samenvatting van de activiteiten van de maand,
            informatie over aankomende activiteiten of bijzonderheden binnen de vereniging en een aantal opmerkelijke citaten die onze leden hebben geroepen.
        </p>
        <p>
            De Gumbode is vrijblijvend, je kan je altijd afmelden.
        </p>

    </div>
</div>
