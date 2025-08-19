<?php
/**
 * Category ordering functionality for KC Categories
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

class Energy_Alabama_KC_Category_Ordering {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_init', array($this, 'init_category_ordering'));
        add_action('wp_ajax_eakc_update_category_order', array($this, 'ajax_update_category_order'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_ordering_scripts'));
        add_filter('terms_clauses', array($this, 'set_category_order'), 10, 3);
    }

    /**
     * Initialize category ordering for KC categories
     */
    public function init_category_ordering() {
        add_action('kc_category_add_form_fields', array($this, 'add_category_order_field'));
        add_action('kc_category_edit_form_fields', array($this, 'edit_category_order_field'));
        add_action('created_kc_category', array($this, 'save_category_order'));
        add_action('edited_kc_category', array($this, 'save_category_order'));
        add_filter('manage_edit-kc_category_columns', array($this, 'add_order_column'));
        add_filter('manage_kc_category_custom_column', array($this, 'populate_order_column'), 10, 3);
        add_action('admin_footer', array($this, 'add_ordering_interface'));
    }

    /**
     * Add order field to category creation form
     */
    public function add_category_order_field() {
        ?>
        <div class="form-field">
            <label for="eakc_category_order"><?php _e('Display Order', 'energy-alabama-kc'); ?></label>
            <input type="number" name="eakc_category_order" id="eakc_category_order" value="0" min="0" step="1">
            <p><?php _e('Enter a number to control the display order. Lower numbers appear first.', 'energy-alabama-kc'); ?></p>
        </div>
        <?php
    }

    /**
     * Add order field to category edit form
     */
    public function edit_category_order_field($term) {
        $order = get_term_meta($term->term_id, 'eakc_category_order', true);
        $order = $order ? intval($order) : 0;
        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="eakc_category_order"><?php _e('Display Order', 'energy-alabama-kc'); ?></label>
            </th>
            <td>
                <input type="number" name="eakc_category_order" id="eakc_category_order" value="<?php echo esc_attr($order); ?>" min="0" step="1">
                <p class="description"><?php _e('Enter a number to control the display order. Lower numbers appear first.', 'energy-alabama-kc'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save category order
     */
    public function save_category_order($term_id) {
        if (isset($_POST['eakc_category_order'])) {
            $order = intval($_POST['eakc_category_order']);
            update_term_meta($term_id, 'eakc_category_order', $order);
        }
    }

    /**
     * Add order column to categories list
     */
    public function add_order_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'name') {
                $new_columns['eakc_order'] = __('Order', 'energy-alabama-kc');
            }
        }
        return $new_columns;
    }

    /**
     * Populate order column
     */
    public function populate_order_column($content, $column_name, $term_id) {
        if ($column_name === 'eakc_order') {
            $order = get_term_meta($term_id, 'eakc_category_order', true);
            return $order ? esc_html($order) : '0';
        }
        return $content;
    }

    /**
     * Set category order in queries
     */
    public function set_category_order($clauses, $taxonomies, $args) {
        global $wpdb;

        if (in_array('kc_category', $taxonomies) && !isset($args['orderby'])) {
            $clauses['join'] .= " LEFT JOIN {$wpdb->termmeta} tm ON t.term_id = tm.term_id AND tm.meta_key = 'eakc_category_order'";
            $clauses['orderby'] = "CAST(tm.meta_value AS UNSIGNED), t.name";
            $clauses['order'] = 'ASC';
        }

        return $clauses;
    }

    /**
     * Enqueue scripts for ordering
     */
    public function enqueue_ordering_scripts($hook) {
        if ($hook === 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'kc_category') {
            wp_enqueue_script('jquery-ui-sortable');
            wp_add_inline_script('jquery-ui-sortable', '
                jQuery(document).ready(function($) {
                    if ($("#the-list").length) {
                        $("#the-list").sortable({
                            axis: "y",
                            helper: "clone",
                            opacity: 0.65,
                            update: function(event, ui) {
                                var order = [];
                                $("#the-list tr").each(function(index) {
                                    var termId = $(this).find(".check-column input").val();
                                    if (termId) {
                                        order.push({
                                            term_id: termId,
                                            order: index
                                        });
                                    }
                                });
                                
                                $.ajax({
                                    url: ajaxurl,
                                    type: "POST",
                                    data: {
                                        action: "eakc_update_category_order",
                                        order: order,
                                        nonce: "' . wp_create_nonce('eakc_category_order_nonce') . '"
                                    },
                                    success: function(response) {
                                        if (response.success) {
                                            // Update order column values
                                            $("#the-list tr").each(function(index) {
                                                $(this).find(".column-eakc_order").text(index);
                                            });
                                        }
                                    }
                                });
                            }
                        });
                        
                        // Add drag cursor to rows
                        $("#the-list tr").css("cursor", "move");
                        
                        // Add notice about drag and drop
                        $(".wrap h1").after("<div class=\"notice notice-info\"><p><strong>Tip:</strong> Drag and drop rows to reorder categories. Changes are saved automatically.</p></div>");
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
        if ($screen && $screen->id === 'edit-kc_category') {
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
     * Handle AJAX request to update category order
     */
    public function ajax_update_category_order() {
        if (!wp_verify_nonce($_POST['nonce'], 'eakc_category_order_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_categories')) {
            wp_die('You do not have permission to reorder categories');
        }

        $order_data = $_POST['order'];
        
        if (!is_array($order_data)) {
            wp_send_json_error('Invalid order data');
        }

        foreach ($order_data as $item) {
            $term_id = intval($item['term_id']);
            $order = intval($item['order']);
            
            if ($term_id > 0) {
                update_term_meta($term_id, 'eakc_category_order', $order);
            }
        }

        wp_send_json_success('Category order updated');
    }
}

// Initialize the class
new Energy_Alabama_KC_Category_Ordering();