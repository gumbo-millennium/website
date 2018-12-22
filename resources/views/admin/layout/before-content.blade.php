{{-- Banner for messages --}}
@if (session('status'))
<div class="alert alert-info">
    {!! session('status') !!}
</div>
@endif

{{-- Banner for errors --}}
@if ($errors->any())
<div class="alert alert-waring">
    @section('validation.error')
    <p>Er ging iets fout bij het laatste verzoek.</p>
    @show
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
