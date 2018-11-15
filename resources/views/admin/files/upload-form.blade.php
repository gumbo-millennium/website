@php
$categoryName = optional($category)->title ?? 'standaard';
@endphp

{{-- Upload modal --}}
<div
    class="modal"
    role="dialog"
    tabindex="-1"
    data-content="upload-form"
    data-upload-content="modal"
    data-upload-csrf="{{ csrf_token() }}"
    data-upload-url="{{ $url }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content admin-files">
            <div class="modal-header">
                <h5 class="modal-title">@lang('files.upload.title')</h5>
            </div>
            <div class="modal-body">
                <div class="admin-files__dropzone" data-upload-content="dropzone">
                    <h3 class="admin-files__title">@lang('files.upload.title')</h3>
                    <p class="admin-files__subtitle">
                        @lang('files.upload.subtitle', ['category' => $categoryName])
                    </p>
                </div>

                <div class="alert alert-info">
                    @lang('files.upload.queue-hint')
                </div>

                <div class="progress admin-files__progress">
                    <div
                        class="progress-bar"
                        role="progressbar"
                        aria-valuenow="0"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        data-upload-content="progress"></div>
                </div>

                <table class="table table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th scope="row">@lang('files.upload.rows.filename')</th>
                            <th scope="row">@lang('files.upload.rows.status')</th>
                            <th scope="row">@lang('files.upload.rows.actions')</th>
                        </tr>
                    </thead>
                    <tbody data-upload-content="queue"></tbody>
                </table>

                <template class="d-none" data-upload-content="template">
                    {{-- Table is removed by Javascript --}}
                    <table role="presentation">
                        <tr data-file-content="row">
                            <td width="60%">
                                <i class="fas fa-fw fa-file-pdf mr-1" aria-label="PDF file"></i>
                                <strong data-dz-name data-file-content="name"></strong>&nbsp;&ndash;
                                <span data-dz-size data-file-content="size"></span>
                                <a class="text-info d-none ml-2" data-file-content="slug"></a>
                            </td>
                            <td width="30%" data-file-content="status"></td>
                            <td width="10%">
                                <a href="#" class="btn btn-danger d-none" data-dz-remove data-file-action="cancel">
                                    <i class="fas fa-times fa-fw" title="{{ __('files.actions.cancel') }}"></i>
                                </a>
                                <a href="#" class="btn btn-brand d-none" data-file-action="link">
                                    <i class="fas fa-external-link-alt fa-fw" title="{{ __('files.actions.view') }}"></i>
                                </a>
                            </td>
                        <tr>
                    </table>
                </template>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" data-upload-action="modal-close">{{ __('files.upload.close') }}</button>
                <button type="button" class="btn btn-primary d-none" data-upload-action="modal-reload">{{ __('files.upload.close-and-reload') }}</button>
            </div>
        </div>
    </div>
</div>
