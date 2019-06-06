{{-- Error handling --}}
@if ($errors->any())
<div class="container gumbo-shaded-block">
    <div class="alert alert-warning">
        <h4 class="alert-title">Oops</h4>
        <p>{{ $errors->count() == 1 ? 'Een veld klopt' : 'Een aantal velden kloppen' }} nog niet, kijk deze even na.</p>
        <ol>
            @foreach ($errors->all() as $field => $error)
            <li>{{ $error }}</li>
            @endforeach
        </ol>
    </div>
</div>
@endif
