{{-- Privacy policy --}}
<div class="my-1">
    <h3>Verwerking persoonsgegevens</h3>
</div>

<div class="row">
    <div class="col-sm-2">
        <label for="accept-policy">Gegevensverwerking</label>
    </div>
    <div class="col-sm-10">
        <div class="custom-control custom-checkbox mb-2">
            <input
            type="checkbox"
            class="custom-control-input {{ $errors->has('accept-policy') ? 'is-invalid' : '' }}"
            name="accept-policy"
            id="accept-policy"
            {{ old('accept-policy') ? 'checked' : '' }} />
            <label class="custom-control-label" for="accept-policy">
                Ik ga akkoord met <a href="{{ route('privacy') }}">het privacybeleid</a> en geef Gumbo Millennium toestemming om mijn gegevens te verwerken.
                </label>
        </div>
    </p>
        <p class="text-muted">
            Om jouw lidmaatschap tot stand te brengen, slaan wij de hierboven ingevulde gegevens op in een online administratiesysteem. Hiervoor
            hebben wij jouw toestemming nodig. <strong>Je gegevens worden niet aan derden verstrekt</strong>.
        </p>
    </div>
</div>
