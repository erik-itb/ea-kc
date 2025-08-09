<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'node_modules', 'tests', '.git'])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(false)
    ->setRules([
        // Basic rules that don't affect functionality
        '@PSR12' => false, // Disable strict PSR12
        '@PSR2' => false,  // Disable strict PSR2
        
        // Only enable essential rules
        'encoding' => true,
        'full_opening_tag' => true,
        'no_closing_tag' => true,
        'no_unused_imports' => false, // Allow unused imports
        'no_whitespace_in_blank_line' => false,
        'trailing_comma_in_multiline' => false,
        
        // Disable strict formatting rules
        'array_indentation' => false,
        'array_syntax' => false, // Don't force [] over array()
        'binary_operator_spaces' => false,
        'blank_line_after_namespace' => false,
        'blank_line_after_opening_tag' => false,
        'blank_line_before_statement' => false,
        'braces' => false,
        'cast_spaces' => false,
        'class_attributes_separation' => false,
        'class_definition' => false,
        'concat_space' => false,
        'declare_equal_normalize' => false,
        'elseif' => false,
        'function_declaration' => false,
        'function_typehint_space' => false,
        'heredoc_to_nowdoc' => false,
        'include' => false,
        'increment_style' => false,
        'indentation_type' => false, // Allow tabs or spaces
        'line_ending' => false,
        'lowercase_cast' => false,
        'lowercase_keywords' => false,
        'lowercase_static_reference' => false,
        'magic_constant_casing' => false,
        'magic_method_casing' => false,
        'method_argument_space' => false,
        'method_chaining_indentation' => false,
        'native_function_casing' => false,
        'native_function_type_declaration_casing' => false,
        'new_with_braces' => false,
        'no_blank_lines_after_class_opening' => false,
        'no_blank_lines_after_phpdoc' => false,
        'no_empty_statement' => false,
        'no_extra_blank_lines' => false,
        'no_leading_import_slash' => false,
        'no_leading_namespace_whitespace' => false,
        'no_mixed_echo_print' => false,
        'no_multiline_whitespace_around_double_arrow' => false,
        'no_short_bool_cast' => false,
        'no_singleline_whitespace_before_semicolons' => false,
        'no_spaces_after_function_name' => false,
        'no_spaces_around_offset' => false,
        'no_spaces_inside_parenthesis' => false,
        'no_trailing_whitespace' => false,
        'no_trailing_whitespace_in_comment' => false,
        'no_unneeded_control_parentheses' => false,
        'no_unneeded_curly_braces' => false,
        'no_unneeded_final_method' => false,
        'no_whitespace_before_comma_in_array' => false,
        'normalize_index_brace' => false,
        'object_operator_without_whitespace' => false,
        'ordered_imports' => false,
        'phpdoc_align' => false,
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_indent' => false,
        'phpdoc_inline_tag_normalizer' => false,
        'phpdoc_no_access' => false,
        'phpdoc_no_alias_tag' => false,
        'phpdoc_no_empty_return' => false,
        'phpdoc_no_package' => false,
        'phpdoc_no_useless_inheritdoc' => false,
        'phpdoc_return_self_reference' => false,
        'phpdoc_scalar' => false,
        'phpdoc_separation' => false,
        'phpdoc_single_line_var_spacing' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'phpdoc_trim' => false,
        'phpdoc_trim_consecutive_blank_line_separation' => false,
        'phpdoc_types' => false,
        'phpdoc_types_order' => false,
        'phpdoc_var_without_name' => false,
        'return_type_declaration' => false,
        'semicolon_after_instruction' => false,
        'short_scalar_cast' => false,
        'single_blank_line_at_eof' => false,
        'single_blank_line_before_namespace' => false,
        'single_class_element_per_statement' => false,
        'single_import_per_statement' => false,
        'single_line_after_imports' => false,
        'single_line_comment_style' => false,
        'single_quote' => false, // Don't force single quotes
        'space_after_semicolon' => false,
        'standardize_not_equals' => false,
        'switch_case_semicolon_to_colon' => false,
        'switch_case_space' => false,
        'ternary_operator_spaces' => false,
        'trim_array_spaces' => false,
        'unary_operator_spaces' => false,
        'visibility_required' => false,
        'whitespace_after_comma_in_array' => false,
    ])
    ->setFinder($finder)
    ->setUsingCache(false); // Disable cache to avoid issues
