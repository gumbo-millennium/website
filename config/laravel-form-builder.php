<?php

declare(strict_types=1);

return [
    'defaults' => [
        'form_class' => 'form',
        'wrapper_class' => 'form__field',
        'wrapper_error_class' => 'form__field--with-error',
        'label_class' => 'form__field-label block text-sm font-medium text-gray-700 mb-1',
        'field_class' => 'form__field shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md',
        'field_error_class' => 'form__field-error block w-full pr-10 border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 sm:text-sm rounded-md',
        'help_block_class' => 'form__field-help mt-2 text-sm text-gray-500',
        'error_class' => 'mt-2 text-sm text-red-600',
        'required_class' => '',

        // Override a class from a field.
        'checkbox' => [
            'wrapper_class' => 'form__field form__field--checkbox',
            'field_class' => 'form__field-input form__field-input--checkbox form-checkbox',
        ],
        'button' => [
            'field_class' => 'btn',
        ],
        'submit' => [
            'field_class' => 'btn btn--brand btn--wide',
        ],
        //'text'                => [
        //    'wrapper_class'   => 'form-field-text',
        //    'label_class'     => 'form-field-text-label',
        //    'field_class'     => 'form-field-text-field',
        //]
        //'radio'               => [
        //    'choice_options'  => [
        //        'wrapper'     => ['class' => 'form-radio'],
        //        'label'       => ['class' => 'form-radio-label'],
        //        'field'       => ['class' => 'form-radio-field'],
        //],
    ],
    // Templates
    'form' => 'laravel-form-builder::form',
    'text' => 'laravel-form-builder::text',
    'textarea' => 'laravel-form-builder::textarea',
    'button' => 'laravel-form-builder::button',
    'buttongroup' => 'laravel-form-builder::buttongroup',
    'radio' => 'laravel-form-builder::radio',
    'checkbox' => 'laravel-form-builder::checkbox',
    'select' => 'laravel-form-builder::select',
    'choice' => 'laravel-form-builder::choice',
    'repeated' => 'laravel-form-builder::repeated',
    'child_form' => 'laravel-form-builder::child_form',
    'collection' => 'laravel-form-builder::collection',
    'static' => 'laravel-form-builder::static',

    // Remove the laravel-form-builder:: prefix above when using template_prefix
    'template_prefix' => '',

    'default_namespace' => '',

    'custom_fields' => [
        //        'datetime' => App\Forms\Fields\Datetime::class
    ],
];
