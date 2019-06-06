@extends('admin.layout.default')

@section('content')
<header class="admin__header">
    <h1 class="admin__title">@lang('files.titles.index') » {{ $category->title }}</h1>
    <a href="{{ route('admin.files.index') }}">« @lang('files.actions.back-to-index')</a>
</header>

@if ($category !== null)
<aside role="complementary" class="my-2 col-sm-12">
    @include('admin.files.upload-form', [
        'url' => route('admin.files.upload', ['category' => $category]),
        'category' => $category
    ])
</aside>
@endif

<h2>@lang('files.titles.category', ['category' => $category->title])</h2>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>@lang('files.headers.file-name')</th>
                <th>@lang('files.headers.file-owner')</th>
                {{-- <th>@lang('files.headers.file-state')</th> --}}
                <th class="text-center">@lang('files.headers.actions')</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($files as $file)
            @include('admin.files.browse-entry', [
                'file' => $file,
                'category' => $category
            ])
            @empty
            <tr>
                <td colspan="4" class="text-center text-muted">
                    @lang('files.messages.no-files')
                </td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">
                    @can('create', App\Models\File::class)
                    <a href="#" data-upload-action="open" data-target="upload-form">
                        <i class="fas fa-fw fa-plus" aria-label="Plus symbol"></i>
                        @lang('files.actions.upload')
                    </a>
                    @else
                    <a href="#" disabled class="text-muted" data-target="no-op">
                        <i class="fas fa-fw fa-plus" aria-label="Plus symbol"></i>
                        @lang('files.actions.upload')
                    </a>
                    @endcan
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- Pagination --}}
<div class="d-flex justify-content-center">
    {{ $files->links() }}
</div>

{{-- Description of states --}}
<h4 class="h3">@lang('files.headers.state-desc')</h4>
<dl class="row">
    @foreach (App\Models\File::STATES as $state => $label)
    <dt class="col-sm-3">{{ __("files.state.{$label}") }}</dt>
    <dd class="col-sm-9">{{ __("files.state-desc.{$label}") }}</dd>
    @endforeach
</dl>

@endsection
