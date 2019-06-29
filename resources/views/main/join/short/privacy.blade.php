{{-- Privacy policy --}}
<div class="row">
    <div class="col-sm-2">
        <label for="accept_policy">Gegevensverwerking</label>
    </div>
    <div class="col-sm-10">
        <div class="custom-control custom-checkbox mb-2">
            <input
            type="checkbox"
            class="custom-control-input {{ $errors->has('accept_policy') ? 'is-invalid' : '' }}"
            name="accept_policy"
            id="accept_policy"
            {{ old('accept_policy') ? 'checked' : '' }} />
            <label class="custom-control-label" for="accept_policy">
                Ik ga akkoord met <a href="{{ route('privacy') }}">het privacybeleid</a> en geef Gumbo Millennium toestemming om mijn gegevens te verwerken.
            </label>
        </div>
    </p>
        <p class="text-muted">
            We hebben je persoonsgegevens nodig voor je lidmaatschap.<br />
            <strong>Je gegevens worden niet aan derden verstrekt</strong>.
        </p>
    </div>
</div>
