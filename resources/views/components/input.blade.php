@props([
    'name',
    'label',
    'type' => 'text',
    'help' => '',
    'value' => '',
    'hideLabel' => false,
])
<?php
$hasError = $errors->has($name);
$hasHelp = ! empty($help);
$groupClass = [
    'mt-1' => true,
    'relative rounded-md shadow-sm' => $hasError,
];
$inputAttributes = $attributes
    ->class([
        'block w-full sm:text-sm rounded-md' => true,
        'shadow-sm focus:ring-brand-500 focus:border-brand-500 border-gray-300' => ! $hasError,
        'pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500' => $hasError,
    ])
    ->merge(array_filter([
        'type' => $type,
        'name' => $name,
        'id' => $name,
        'aria-invalid' => $hasError ? 'true' : null,
        'aria-describedby' => $hasError ? "{$name}-error" : ($hasHelp ? "{$name}-help" : null),
        'aria-labelledby' => $hideLabel ? null : "{$name}-label",
        'aria-label' => $hideLabel ? $label : null,
        'value' => $type === 'password' ? null : (old($name) ?? $value),
    ]));
?>
<div>
  @unless ($hideLabel)
  <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
  @endunless

  <div @class($groupClass)>
    @if ($input ?? null)
      {{ $input }}
    @else
      <input {{ $inputAttributes }}>
    @endif
    @if ($hasError)
    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
      <x-icon icon="solid/circle-exclamation" class="h-5 w-5 text-red-500" />
    </div>
    @endif
  </div>

  @if ($hasError)
  <p class="mt-2 text-sm text-red-600" id="{{ $name }}-error">{{ $errors->get($name)[0] }}</p>
  @elseif ($hasHelp)
  <p class="mt-2 text-sm text-gray-500" id="{{ $name }}-description">{{ $help }}</p>
  @endif
</div>
