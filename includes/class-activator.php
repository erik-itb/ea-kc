<?php

/**
 * Fired during plugin activation
 *
 * @link       https://energyalabama.org
 * @since      1.0.0
 *
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes
 * @author     Energy Alabama <info@energyalabama.org>
 */
class Energy_Alabama_KC_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		// Create default plugin options
		self::create_default_options();
		
		// Register post types and taxonomies for rewrite rules
		self::register_post_types_and_taxonomies();
		
		// Set flag to flush rewrite rules
		update_option( 'eakc_flush_rewrite_rules', true );
		
		// Create default taxonomy terms
		self::create_default_taxonomy_terms();
		
		// Set plugin version
		update_option( 'energy_alabama_kc_version', ENERGY_ALABAMA_KC_VERSION );
		
		// Set activation timestamp
		update_option( 'energy_alabama_kc_activated', current_time( 'timestamp' ) );

	}

	/**
	 * Create default plugin options.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static function create_default_options() {
		$default_options = array(
			'search_results_per_page' => 10,
			'enable_spanish_content' => 0,
			'articles_per_page' => 12,
			'dockets_per_page' => 20,
		);

		// Only add defaults if options don't exist
		if ( ! get_option( 'energy_alabama_kc_options' ) ) {
			add_option( 'energy_alabama_kc_options', $default_options );
		}
	}

	/**
	 * Register post types and taxonomies for rewrite rules.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static function register_post_types_and_taxonomies() {
		// Load the core classes
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-post-types.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-taxonomies.php';

		// Register post types and taxonomies
		$post_types = new Energy_Alabama_KC_Post_Types();
		$taxonomies = new Energy_Alabama_KC_Taxonomies();

		$post_types->register_post_types();
		$taxonomies->register_taxonomies();
	}

	/**
	 * Create default taxonomy terms.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static function create_default_taxonomy_terms() {
		// Default KC Categories
		$default_categories = array(
			array(
				'name' => 'Clean Energy and Energy Efficiency',
				'slug' => 'clean-energy-and-energy-efficiency',
				'description' => 'Fundamental information about clean energy technologies and concepts.',
			),
			array(
				'name' => 'Energy Efficiency',
				'slug' => 'energy-efficiency',
				'description' => 'Information about energy efficiency measures and weatherization.',
			),
			array(
				'name' => 'Educator Resources',
				'slug' => 'educator-resources',
				'description' => 'Materials and resources for educators and professional development.',
			),
			array(
				'name' => 'Legal and Regulatory',
				'slug' => 'legal-regulatory',
				'description' => 'Legal documents, regulations, and regulatory information.',
			),
			array(
				'name' => 'Presentation Library',
				'slug' => 'presentation-library',
				'description' => 'Collection of presentations and visual materials.',
			),
			array(
				'name' => 'FAQs',
				'slug' => 'faqs',
				'description' => 'Frequently asked questions and answers.',
			),
		);

		foreach ( $default_categories as $category ) {
			if ( ! term_exists( $category['slug'], 'kc_category' ) ) {
				wp_insert_term(
					$category['name'],
					'kc_category',
					array(
						'slug' => $category['slug'],
						'description' => $category['description'],
					)
				);
			}
		}

		// Default Docket Types
		$default_docket_types = array(
			array(
				'name' => 'Rate Case',
				'slug' => 'rate-case',
				'description' => 'Utility rate adjustment proceedings.',
			),
			array(
				'name' => 'Certificate',
				'slug' => 'certificate',
				'description' => 'Certificate applications and proceedings.',
			),
			array(
				'name' => 'Rulemaking',
				'slug' => 'rulemaking',
				'description' => 'Regulatory rulemaking proceedings.',
			),
			array(
				'name' => 'Investigation',
				'slug' => 'investigation',
				'description' => 'Regulatory investigations and inquiries.',
			),
			array(
				'name' => 'Complaint',
				'slug' => 'complaint',
				'description' => 'Formal complaints and proceedings.',
			),
		);

		foreach ( $default_docket_types as $docket_type ) {
			if ( ! term_exists( $docket_type['slug'], 'docket_type' ) ) {
				wp_insert_term(
					$docket_type['name'],
					'docket_type',
					array(
						'slug' => $docket_type['slug'],
						'description' => $docket_type['description'],
					)
				);
			}
		}

		// Default KC Tags
		$default_tags = array(
			'Solar', 'Wind', 'Nuclear', 'Electric Vehicles', 'Weatherization', 
			'PSC', 'Alabama Power', 'Renewable Energy', 'Grid', 'Efficiency'
		);

		foreach ( $default_tags as $tag ) {
			if ( ! term_exists( $tag, 'kc_tags' ) ) {
				wp_insert_term( $tag, 'kc_tags' );
			}
		}
	}

}