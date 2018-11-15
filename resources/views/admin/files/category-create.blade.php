@extends('admin.layout.default')

@section('content')
<header class="admin__header">
    <h1 class="admin__title">@lang('files.titles.name')</h1>
</header>

<h2 class="my-2">@lang('files.titles.category-create')</h2>

<p>
    Vul onderstaand formulier in om een nieuwe categorie toe te voegen.
</p>

<form method="POST" action="{{ route('admin.files.category.create') }}">
    @csrf

    {{-- Name --}}
    <div class="row form-group">
        <label for="title" class="col-form-label col-sm-3">Titel</label>
        <div class="col-sm-9">
            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required />
        </div>
    </div>

    {{-- Default label --}}
    <div class="row form-group">
        <div class="col-sm-9 offset-sm-3">
            <div class="custom-control custom-checkbox">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="default"
                    name="default"
                    {{ old('default') ? 'checked' : '' }}>
                <label class="custom-control-label" for="default">Standaard categorie</label>
            </div>
        </div>
    </div>

    {{-- Send button --}}
    <div class="row form-group">
        <div class="col-sm-9 offset-sm-3">
            <button type="submit" class="btn btn-primary">Toevoegen</button>
        </div>
    </div>
</form>

@endsection
