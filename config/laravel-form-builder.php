<?php

declare(strict_types=1);

return [
    'defaults' => [
        'form_class'            => 'form',
        'wrapper_class'         => 'form__field',
        'wrapper_error_class'   => 'form__field--with-error',
        'label_class'           => 'form__field-label',
        'field_class'           => 'form__field-input form-input',
        'field_error_class'     => 'form__field-input--error',
        'help_block_class'      => 'form__field-help',
        'error_class'           => 'form__field-error',
        'required_class'        => 'form__field-label--required',

        // Override a class from a field.
        'checkbox' => [
            'wrapper_class' => 'form__field form__field--checkbox',
            'field_class'   => 'form__field-input form__field-input--checkbox form-checkbox',
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
    'form'          => 'laravel-form-builder::form',
    'text'          => 'laravel-form-builder::text',
    'textarea'      => 'laravel-form-builder::textarea',
    'button'        => 'laravel-form-builder::button',
    'buttongroup'   => 'laravel-form-builder::buttongroup',
    'radio'         => 'laravel-form-builder::radio',
    'checkbox'      => 'laravel-form-builder::checkbox',
    'select'        => 'laravel-form-builder::select',
    'choice'        => 'laravel-form-builder::choice',
    'repeated'      => 'laravel-form-builder::repeated',
    'child_form'    => 'laravel-form-builder::child_form',
    'collection'    => 'laravel-form-builder::collection',
    'static'        => 'laravel-form-builder::static',

    // Remove the laravel-form-builder:: prefix above when using template_prefix
    'template_prefix'   => '',

    'default_namespace' => '',

    'custom_fields' => [
//        'datetime' => App\Forms\Fields\Datetime::class
    ],
];
