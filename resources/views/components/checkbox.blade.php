@props([
  'name',
  'label',
  'type' => 'checkbox',
  'help' => '',
  'value' => null,
])
<?php
$hasError = $errors->has($name);
$hasHelp = ! empty ($help);
$inputClass = [
  'h-4 w-4',
  'rounded' => $type === 'checkbox',
  'rounded-full' => $type === 'radio',
  'text-brand-600 border-gray-300',
  'hover:ring-brand-500' => !$hasError,
  'ring-red-500 hover:ring-red-600' => $hasError,
];
$inputAttributes = array_filter([
  'id' => $name,
  'name' => $name,
  'type' => $type,
  'aria-invalid' => $hasError ? 'true' : null,
  'aria-labelledby' => "$name-label",
  'aria-describedby' => $hasError ? "$name-error" : ($hasHelp ? "$name-help" : null),
  'checked' => match($type) {
    'checkbox' => (old($name) ? 'checked' : $value),
    'radio' => (array_has(Arr::wrap(old($name) ?? $value), $name) ? 'checked' : null)
  },
]);
?>
<div class="relative flex items-start">
  <div class="flex items-center h-5">
    <input {{ $attributes->class($inputClass)->merge($inputAttributes) }}>
  </div>
  <div class="ml-3 space-y-2">
    <label for="{{ $name }}" id="{{ $name }}-label" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    @if ($hasError)
    <p id="{{ $name }}-error" class="text-red-500 text-sm">
      {{ $errors->get($name)[0] }}
    </p>
    @elseif ($hasHelp)
    <p id="{{ $name }}-description" class="text-gray-500 text-sm">
      {{ $help }}
    </p>
    @endif
  </div>
</div>
