@extends('admin.layout.default')

@section('content')
<header class="admin__header">
    <h1 class="admin__title">@lang('files.titles.name')</h1>
</header>

<h2>@lang('files.titles.category-remove', ['category' => $category->name])</h2>

<div class="row">
    <div class="col-md-10 offset-md-1 my-3">
        <form method="POST" class="card">
            @csrf
            @method('DELETE')
            <h5 class="card-header">Categorie verwijderen</h5>
            <div class="card-body">
                <p>Weet je zeker dat je de categorie {{ $category->name }} wilt verwijderen?</p>
                <p>Deze actie kan niet ongedaan gemaakt worden!</p>
                <hr />
                <p>De categorie bevat {{ $fileCount }} {{ trans_choice('files.plurals.files', $fileCount)}}.</p>
            </div>
            <div class="card-footer text-right">
                <a href="{{ route('admin.files.index') }}" class="btn btn-primary">Annuleren</a>
                <button type="submit" class="btn btn-outline-danger">Categorie verwijderen</button>
            </div>
        </form>
    </div>
</div>

@endsection
