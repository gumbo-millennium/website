@php
// Update label
$field = (object) array_merge([
    'type' => $field[0] ?? 'text',
    'name' => $field[1] ?? null,
    'label' => $field[2] ?? 'Field',
    'placeholder' => $field[4] ?? null,
    'help' => $field[3] ?? null,
    'required' => true
], $field);

// Get value
if (property_exists($field, 'value')) {
    $field->value = old($field->name) ?? $field->value ?? null;
} else {
    $field->value = old($field->name) ?? null;
}

$statusClass = '';
if ($errors->has($field->name)) {
    $statusClass .= 'is-invalid';
} elseif ($errors->any() && old($field->name)) {
    $statusClass .= 'is-valid';
}
@endphp
@props([
    'field',
    'label' => 'Label',
])
<div class="form-group row">
    @if ($field->type !== 'checkbox')
    <label for="{{ $field->name }}" class="col-sm-2 col-form-label">
        {{ $label }}
    </label>
    @else
    <div class="col-sm-2"></div>
    @endif
    <div class="col-sm-10">
        @if ($field->type === 'checkbox')
        <div class="custom-control custom-checkbox">
            <input
                type="checkbox"
                class="custom-control-input {{ $statusClass }}"
                name="{{ $field->name }}"
                id="{{ $field->name }}"
                {{ old($field->name) ? 'checked' : '' }}
                {{ $field->required ? 'required' : '' }}
                />
            <label class="custom-control-label" for="{{ $field->name }}">
                {{ $label}}
            </label>
        </div>
        @elseif($field->type === 'select')
        <select
            class="form-control {{ $statusClass }}"
            id="{{ $field->name }}"
            name="{{ $field->name }}"
            {{ $field->required ? 'required' : '' }}
            >
            <option>- Selecteer -</option>
            @foreach ($field->options as $option => $value)
                <option value="{{ $option }}" {{ $field->value === $option ? 'selected' : '' }}>{{ $value }}</option>
            @endforeach
            </select>
        @else
        <input
            class="form-control {{ $statusClass }}"
            type="{{ $field->type }}"
            id="{{ $field->name }}"
            name="{{ $field->name }}"
            placeholder="{{ $field->placeholder ?? null }}"
            value="{{ $field->value }}"
            {{ $field->required ? 'required' : '' }}
            />
        @endif
        @if ($errors->has($field->name))
        <p class="mt-1 text-danger">{{ implode(', ', $errors->get($field->name)) }}</p>
        @elseif (!empty($field->help))
        <p class="mt-1 text-muted">{{ $field->help }}</p>
        @endif
    </div>
</div>
