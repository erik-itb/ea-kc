<?php
/**
 * FAQ ordering functionality for FAQ post type
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Energy_Alabama_KC_FAQ_Ordering {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init_faq_ordering'));
        add_action('wp_ajax_eakc_update_faq_order', array($this, 'ajax_update_faq_order'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_ordering_scripts'));
        add_filter('pre_get_posts', array($this, 'set_faq_order'));
    }

    /**
     * Initialize FAQ ordering for FAQ post type
     */
    public function init_faq_ordering() {
        add_filter('manage_faq_posts_columns', array($this, 'add_order_column'));
        add_action('manage_faq_posts_custom_column', array($this, 'populate_order_column'), 10, 2);
        add_action('admin_footer', array($this, 'add_ordering_interface'));
        add_filter('edit_faq_per_page', array($this, 'set_faq_per_page'));
    }

    /**
     * Add order column to FAQ list
     */
    public function add_order_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['eakc_order'] = __('Order', 'energy-alabama-kc');
            }
        }
        return $new_columns;
    }

    /**
     * Populate order column
     */
    public function populate_order_column($column_name, $post_id) {
        if ($column_name === 'eakc_order') {
            $order = get_post_field('menu_order', $post_id);
            echo esc_html($order);
        }
    }

    /**
     * Set FAQ order in queries
     */
    public function set_faq_order($query) {
        if (!is_admin() && $query->is_main_query() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'faq') {
            $query->set('orderby', 'menu_order');
            $query->set('order', 'ASC');
        }
        
        // Also apply to admin queries for FAQ post type
        if (is_admin() && $query->get('post_type') === 'faq' && !$query->get('orderby')) {
            $query->set('orderby', 'menu_order');
            $query->set('order', 'ASC');
        }
    }

    /**
     * Enqueue scripts for ordering
     */
    public function enqueue_ordering_scripts($hook) {
        if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'faq') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_add_inline_script('jquery-ui-sortable', '
                jQuery(document).ready(function($) {
                    if ($("#the-list").length) {
                        $("#the-list").sortable({
                            axis: "y",
                            helper: "clone",
                            opacity: 0.65,
                            update: function(event, ui) {
                                // Update order column values immediately after drop
                                $("#the-list tr").each(function(index) {
                                    $(this).find(".column-eakc_order").text(index);
                                });
                                
                                var order = [];
                                $("#the-list tr").each(function(index) {
                                    var postId = $(this).find(".check-column input").val();
                                    if (postId) {
                                        order.push({
                                            post_id: postId,
                                            order: index
                                        });
                                    }
                                });
                                
                                $.ajax({
                                    url: ajaxurl,
                                    type: "POST",
                                    data: {
                                        action: "eakc_update_faq_order",
                                        order: order,
                                        nonce: "' . wp_create_nonce('eakc_faq_order_nonce') . '"
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            // Order numbers already updated above, but we could add a success indicator here
                                            console.log("FAQ order saved successfully");
                                        }
                                    },
                                    error: function() {
                                        // If save fails, we might want to revert the visual changes
                                        alert("Failed to save FAQ order. Please try again.");
                                    }
                                });
                            }
                        });
                        
                        // Add drag cursor to rows
                        $("#the-list tr").css("cursor", "move");
                        
                        // Add notice about drag and drop
                        $(".wrap h1").after("<div class=\"notice notice-info\"><p><strong>Tip:</strong> Drag and drop rows to reorder FAQs. Changes are saved automatically.</p></div>");
                    }
                });
            ');
        }
    }

    /**
     * Add ordering interface styles
     */
    public function add_ordering_interface() {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'edit-faq') {
            ?>
            <style>
                #the-list tr {
                    cursor: move !important;
                }
                #the-list tr:hover {
                    background-color: #f0f8ff;
                }
                .ui-sortable-helper {
                    background-color: #fff;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                .column-eakc_order {
                    width: 80px;
                    text-align: center;
                }
            </style>
            <?php
        }
    }

    /**
     * Handle AJAX request to update FAQ order
     */
    public function ajax_update_faq_order() {
        if (!wp_verify_nonce($_POST['nonce'], 'eakc_faq_order_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('edit_posts')) {
            wp_die('You do not have permission to reorder FAQs');
        }

        $order_data = $_POST['order'];
        
        if (!is_array($order_data)) {
            wp_send_json_error('Invalid order data');
        }

        foreach ($order_data as $item) {
            $post_id = intval($item['post_id']);
            $order = intval($item['order']);
            
            if ($post_id > 0) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'menu_order' => $order
                ));
            }
        }

        wp_send_json_success('FAQ order updated');
    }

    /**
     * Set FAQ posts per page to show all items (no pagination)
     */
    public function set_faq_per_page($per_page) {
        // Set to a very high number to effectively show all FAQs
        // This ensures drag and drop works across all items
        return 999;
    }
}

// Initialize the class
new Energy_Alabama_KC_FAQ_Ordering();