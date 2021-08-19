<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        '.deploy',
        'app',
        'config',
        'database',
        'resources/lang',
        'routes',
        'tests',
    ]);

return (new Config())
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setCacheFile('.php-cs-fixer.cache')
    ->setRules([
        '@PSR12' => true,
        '@PHP74Migration:risky' => true,

        // Alias
        'no_mixed_echo_print' => ['use' => 'echo'],

        // Array Notation
        'array_syntax' => ['syntax' => 'short'],
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,

        // Casing
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,

        // Cast Notation
        'cast_spaces' => true,
        'modernize_types_casting' => true,
        'no_short_bool_cast' => true,
        'no_unset_cast' => true,

        // Class Notation
        'class_attributes_separation' => true,
        'no_null_property_initialization' => true,
        'no_unneeded_final_method' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',

                'constant_public',
                'constant_protected',
                'constant_private',

                'property_public_static',
                'property_protected_static',
                'property_private_static',

                'property_public',
                'property_protected',
                'property_private',

                'method_public_static',
                'method_protected_static',
                'method_private_static',

                'construct',
                'destruct',

                'phpunit',

                'method_abstract',

                'method_public',
                'method_protected',
                'method_private',

                'magic',
            ],
        ],
        'ordered_interfaces' => true,
        'ordered_traits' => true,
        'self_accessor' => true,
        'single_class_element_per_statement' => [
            'elements' => [
                'const',
                'property',
            ],
        ],

        // Comment
        'comment_to_phpdoc' => true,
        'header_comment' => ['header' => ''],
        'multiline_comment_opening_closing' => true,
        'single_line_comment_style' => true,

        // Control Structure
        'no_alternative_syntax' => true,
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => [
                'arrays',
                'arguments',
            ],
        ],

        // Function Notation
        'function_typehint_space' => true,
        'implode_call' => true,
        'lambda_not_used_import' => true,
        'no_useless_sprintf' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'void_return' => false,

        // Import
        'no_unused_imports' => true,
        'global_namespace_import' => [
            'import_constants' => false,
            'import_functions' => false,
            'import_classes' => true,
        ],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],

        // Language Constructs
        'explicit_indirect_variable' => true,
        'function_to_constant' => true,
        'is_null' => true,

        // List Notation
        'list_syntax' => ['syntax' => 'short'],

        // Operator
        'not_operator_with_successor_space' => true,
        'standardize_not_equals' => true,
        'ternary_to_null_coalescing' => true,

        // PHPUnit
        'php_unit_method_casing' => ['case' => 'snake_case'],
        'php_unit_test_annotation' => ['style' => 'prefix'],

        // PHPDoc
        'align_multiline_comment' => true,
        'general_phpdoc_annotation_remove' => [
            'annotations' => [
                'author',
                'license',
                'package',
                'version',
            ],
        ],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => true,
        'phpdoc_indent' => true,
        'phpdoc_line_span' => true,
        'phpdoc_scalar' => true,
        'phpdoc_summary' => true,
        'phpdoc_trim' => true,
        'phpdoc_types_order' => ['null_adjustment' => 'always_first'],

        // Return Notation
        'no_useless_return' => true,

        // Strict
        'declare_strict_types' => true,
        'strict_param' => true,

        // String Notation
        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'single_quote' => true,

        // Whitespace
        'array_indentation' => true,
        'blank_line_before_statement' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'curly_brace_block',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'throw',
                'use',
                'use_trait',
                'switch',
                'case',
                'default',
            ],
        ],
        'no_spaces_around_offset' => true,
    ]);
