<?php
/**
 * Admin menu hierarchy styling for Knowledge Center
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

class Energy_Alabama_KC_Menu_Hierarchy {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'customize_menu_hierarchy'), 999);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_hierarchy_styles'));
        add_action('admin_footer', array($this, 'add_hierarchy_script'));
    }

    /**
     * Customize menu hierarchy and add visual indicators
     */
    public function customize_menu_hierarchy() {
        global $submenu;
        
        // Check if the Knowledge Center menu exists
        if (!isset($submenu['edit.php?post_type=kc_article'])) {
            return;
        }

        // Remove existing items and rebuild in hierarchical order
        $submenu['edit.php?post_type=kc_article'] = array();
        
        // Dashboard (top level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Knowledge Center Dashboard', 'energy-alabama-kc'),
            __('Dashboard', 'energy-alabama-kc'),
            'manage_options',
            'energy-alabama-kc-dashboard',
            array($this, 'dashboard_callback')
        );

        // All KC Articles (top level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('KC Articles', 'energy-alabama-kc'),
            __('All KC Articles', 'energy-alabama-kc'),
            'edit_posts',
            'edit.php?post_type=kc_article',
            ''
        );

        // Add New KC Article (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Add New KC Article', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Add New KC Article', 'energy-alabama-kc') . '</span>',
            'edit_posts',
            'post-new.php?post_type=kc_article',
            ''
        );

        // Categories (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Categories', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Categories', 'energy-alabama-kc') . '</span>',
            'manage_categories',
            'edit-tags.php?taxonomy=kc_category&post_type=kc_article',
            ''
        );

        // Tags (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Tags', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Tags', 'energy-alabama-kc') . '</span>',
            'manage_categories',
            'edit-tags.php?taxonomy=kc_tag&post_type=kc_article',
            ''
        );

        // All Dockets (top level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Dockets', 'energy-alabama-kc'),
            __('All Dockets', 'energy-alabama-kc'),
            'edit_posts',
            'edit.php?post_type=docket',
            ''
        );

        // Add New Docket (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Add New Docket', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Add New Docket', 'energy-alabama-kc') . '</span>',
            'edit_posts',
            'post-new.php?post_type=docket',
            ''
        );

        // Jurisdictions (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Jurisdictions', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Jurisdictions', 'energy-alabama-kc') . '</span>',
            'manage_categories',
            'edit-tags.php?taxonomy=docket_jurisdiction&post_type=docket',
            ''
        );

        // All Definitions (top level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('All Definitions', 'energy-alabama-kc'),
            __('All Definitions', 'energy-alabama-kc'),
            'edit_posts',
            'edit.php?post_type=glossary',
            ''
        );

        // Add New Definition (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Add New Definition', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Add New Definition', 'energy-alabama-kc') . '</span>',
            'edit_posts',
            'post-new.php?post_type=glossary',
            ''
        );

        // Import Definitions (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Import Definitions', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Import Definitions', 'energy-alabama-kc') . '</span>',
            'manage_options',
            'energy-alabama-kc-glossary-import',
            array($this, 'import_callback')
        );

        // All FAQs (top level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('All FAQs', 'energy-alabama-kc'),
            __('All FAQs', 'energy-alabama-kc'),
            'edit_posts',
            'edit.php?post_type=faq',
            ''
        );

        // Add New FAQ (sub-level)
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Add New FAQ', 'energy-alabama-kc'),
            '<span class="eakc-submenu-item">→ ' . __('Add New FAQ', 'energy-alabama-kc') . '</span>',
            'edit_posts',
            'post-new.php?post_type=faq',
            ''
        );
    }

    /**
     * Enqueue hierarchy styles
     */
    public function enqueue_hierarchy_styles() {
        // Only on admin pages
        if (!is_admin()) {
            return;
        }

        wp_add_inline_style('admin-menu', $this->get_hierarchy_css());
    }

    /**
     * Get CSS for menu hierarchy
     */
    private function get_hierarchy_css() {
        return '
        /* Knowledge Center Menu Hierarchy Styles */
        #adminmenu .wp-submenu .eakc-submenu-item {
            color: #82878c;
            font-size: 13px;
            display: block;
        }
        
        /* Sub-item styling (will be applied via JavaScript) */
        #adminmenu .wp-submenu li.eakc-sub-item {
            position: relative;
        }
        
        #adminmenu .wp-submenu li.eakc-sub-item a {
            padding-left: 20px !important;
            background: rgba(240, 245, 249, 0.05) !important;
            border-left: 3px solid #0073aa !important;
            margin-left: 8px !important;
            border-radius: 0 3px 3px 0 !important;
        }
        
        #adminmenu .wp-submenu li.eakc-sub-item a:hover {
            background: rgba(240, 245, 249, 0.1) !important;
            color: #0073aa !important;
        }
        
        #adminmenu .wp-submenu li.eakc-sub-item a:focus {
            background: rgba(240, 245, 249, 0.1) !important;
            color: #0073aa !important;
            box-shadow: none !important;
        }
        
        /* Arrow styling */
        .eakc-submenu-item::before {
            content: "→";
            color: #82878c;
            margin-right: 6px;
            font-weight: normal;
            font-size: 12px;
        }
        
        /* Top-level items styling */
        #adminmenu .wp-submenu li.eakc-top-item a {
            font-weight: 600 !important;
            color: #fff !important;
        }
        
        /* Hover effect for arrow */
        #adminmenu .wp-submenu li.eakc-sub-item a:hover .eakc-submenu-item::before {
            color: #0073aa;
        }
        
        /* Responsive adjustments */
        @media (max-width: 960px) {
            #adminmenu .wp-submenu li.eakc-sub-item a {
                padding-left: 16px !important;
                margin-left: 4px !important;
            }
        }
        ';
    }

    /**
     * Add JavaScript for enhanced hierarchy behavior
     */
    public function add_hierarchy_script() {
        if (!is_admin()) {
            return;
        }
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Add hierarchy classes for better targeting
            $('#adminmenu .wp-submenu a').each(function() {
                if ($(this).find('.eakc-submenu-item').length > 0) {
                    $(this).closest('li').addClass('eakc-sub-item');
                } else {
                    $(this).closest('li').addClass('eakc-top-item');
                }
            });
            
            // Enhanced hover effects
            $('.eakc-sub-item a').hover(
                function() {
                    $(this).find('.eakc-submenu-item').css('color', '#0073aa');
                },
                function() {
                    $(this).find('.eakc-submenu-item').css('color', '#82878c');
                }
            );
        });
        </script>
        <?php
    }

    /**
     * Dashboard page callback
     */
    public function dashboard_callback() {
        // Access admin through the global plugin instance
        global $energy_alabama_kc;
        if (isset($energy_alabama_kc) && method_exists($energy_alabama_kc, 'get_admin')) {
            $admin = $energy_alabama_kc->get_admin();
            if (method_exists($admin, 'display_dashboard_page')) {
                $admin->display_dashboard_page();
                return;
            }
        }
        
        // Fallback: simple message
        echo '<div class="wrap"><h1>' . __('Knowledge Center Dashboard', 'energy-alabama-kc') . '</h1><p>' . __('Dashboard functionality coming soon.', 'energy-alabama-kc') . '</p></div>';
    }

    /**
     * Import page callback
     */
    public function import_callback() {
        // Access admin through the global plugin instance
        global $energy_alabama_kc;
        if (isset($energy_alabama_kc) && method_exists($energy_alabama_kc, 'get_admin')) {
            $admin = $energy_alabama_kc->get_admin();
            if (method_exists($admin, 'display_glossary_import_page')) {
                $admin->display_glossary_import_page();
                return;
            }
        }
        
        // Fallback: simple message
        echo '<div class="wrap"><h1>' . __('Import Definitions', 'energy-alabama-kc') . '</h1><p>' . __('Import functionality coming soon.', 'energy-alabama-kc') . '</p></div>';
    }
}

// Initialize the class
new Energy_Alabama_KC_Menu_Hierarchy();