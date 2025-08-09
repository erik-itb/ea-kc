<?php
/**
 * Simple Test Template for Knowledge Center Landing Page
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div style="padding: 40px; background: #f9f9f9;">
    <h1>Knowledge Center Landing Page</h1>
    <p>Template is working! This is a test.</p>

    <?php
    // Test if our classes are working
    echo '<h2>Debug Info:</h2>';
    echo '<p>Template Manager exists: ' . (class_exists('Energy_Alabama_KC_Template_Manager') ? 'Yes' : 'No') . '</p>';
    echo '<p>Current page ID: ' . get_the_ID() . '</p>';
    echo '<p>Current page slug: ' . $post->post_name . '</p>';
    
    // Test categories
    $categories = get_terms(array(
        'taxonomy' => 'kc_category',
        'hide_empty' => false
    ));
    
    echo '<h3>Categories found: ' . count($categories) . '</h3>';
    if (!empty($categories)) {
        echo '<ul>';
        foreach ($categories as $cat) {
            echo '<li>' . esc_html($cat->name) . ' (' . $cat->count . ' articles)</li>';
        }
        echo '</ul>';
    }
    ?>
</div>

<?php get_footer(); ?>