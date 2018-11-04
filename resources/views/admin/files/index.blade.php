@extends('admin.layout.default')

@section('content')
<header class="admin__header">
    <h1 class="admin__title">@lang('files.titles.name')</h1>
</header>

@if ($defaultCategory !== null)
@include('admin.files.upload-form', [
    'url' => route('admin.files.upload'),
    'category' => $defaultCategory
])
@endif

<h2>@lang('files.titles.index')</h2>

<div class="row">
    @forelse ($categories as $category)
    <div class="col-md-6 col-xl-4 mb-2">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('admin.files.list', ['category' => $category]) }}">
                    <i class="fas fa-fw fa-folder"></i>
                    {{ $category->title }}
                    <span class="badge badge-light align-self-end">{{ $category->files()->count() }}</span>
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="alert alert-light text-center">
        <strong>@lang('files.messages.no-categories')</strong>
    </div>
    @endforelse
</div>
@endsection
