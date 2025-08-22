<?php
/**
 * Template for Knowledge Center Landing Page
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
	exit;
}

get_header();

// Helper class for icons - only declare if it doesn't exist
if (!class_exists('EAKC_Landing_Template_Helpers')) {
	class EAKC_Landing_Template_Helpers {

		public function get_category_icon( $slug ) {
			$icons = array(
				'clean-energy-and-energy-efficiency'     => 'energy',
				'educator-resources'   => 'education',
				'legal-regulatory'     => 'legal',
				'presentation-library' => 'presentation',
			);

			return isset($icons[$slug]) ? $icons[$slug] : 'default';
		}

		public function render_category_icon( $slug ) {
			$icon_type = $this->get_category_icon($slug);

			$icons = array(
				'energy'       => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
				'education'    => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path><path d="M6 12v5c3 3 9 3 12 0v-5"></path></svg>',
				'legal'        => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline></svg>',
				'presentation' => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="12" rx="2"></rect><path d="M12 16v4"></path><path d="M8 20h8"></path></svg>',
				'help'         => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
				'default'      => '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>',
			);

			return isset($icons[$icon_type]) ? $icons[$icon_type] : $icons['default'];
		}
	}
}

// Get template manager instance for helper functions
$template_manager = Energy_Alabama_KC_Template_Manager::get_instance();
$categories       = $template_manager->get_kc_categories();
$recent_articles  = $template_manager->get_recent_articles(3);
$helpers          = new EAKC_Landing_Template_Helpers();
?>

<div class="eakc-landing-page">
	
	<!-- Hero Section with Search -->
	<section class="eakc-hero">
		<div class="eakc-container">
			<div class="eakc-hero-content">
				<h1 class="eakc-hero-title">
					<?php _e('Energy Alabama<br>Knowledge Center', 'energy-alabama-kc'); ?>
				</h1>
				<p class="eakc-hero-description">
					<?php _e('Find comprehensive information about clean energy, educational resources, and regulatory documents.', 'energy-alabama-kc'); ?>
				</p>
				
				<!-- Search Form -->
				<div class="eakc-search-container">
					<form class="eakc-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
						<div class="eakc-search-wrapper">
							<input type="search" 
									class="eakc-search-input" 
									placeholder="<?php esc_attr_e('Search knowledge center...', 'energy-alabama-kc'); ?>"
									value="<?php echo get_search_query(); ?>" 
									name="s" 
									autocomplete="off"
									aria-label="<?php esc_attr_e('Search knowledge center', 'energy-alabama-kc'); ?>">
							<input type="hidden" name="post_type" value="kc_article">
							<button type="submit" class="eakc-search-button" aria-label="<?php esc_attr_e('Search', 'energy-alabama-kc'); ?>">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<circle cx="11" cy="11" r="8"></circle>
									<path d="m21 21-4.35-4.35"></path>
								</svg>
							</button>
						</div>
						<div class="eakc-search-results" style="display: none;"></div>
					</form>
				</div>
			</div>
		</div>
	</section>

	<!-- Categories Section -->
	<section class="eakc-categories">
		<div class="eakc-container">
			<h2 class="eakc-section-title">
				<?php _e('Browse by Category', 'energy-alabama-kc'); ?>
			</h2>
			
			<?php if (!empty($categories)) : ?>
				<div class="eakc-category-grid">
					<?php
					foreach ($categories as $category) :
						$category_link = get_term_link($category);
						$article_count = $category->count;
					?>
						<div class="eakc-category-card">
							<a href="<?php echo esc_url($category_link); ?>" class="eakc-category-link">
								<div class="eakc-category-icon">
									<?php echo $helpers->render_category_icon($category->slug); ?>
								</div>
								<h3 class="eakc-category-title"><?php echo esc_html($category->name); ?></h3>
								<p class="eakc-category-description"><?php echo esc_html($category->description); ?></p>
								<span class="eakc-category-count">
									<?php
									printf(
										_n('%s article', '%s articles', $article_count, 'energy-alabama-kc'),
										number_format_i18n($article_count)
									);
									?>
								</span>
							</a>
						</div>
					<?php endforeach; ?>

					<?php
					// Add FAQ card before glossary
					$faq_count = wp_count_posts('faq');
					$total_faqs = $faq_count->publish;
					?>
					<div class="eakc-category-card">
						<a href="<?php echo esc_url(home_url('/knowledge-center/faqs/')); ?>" class="eakc-category-link">
							<div class="eakc-category-icon">
								<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<circle cx="12" cy="12" r="10"></circle>
									<path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
									<line x1="12" y1="17" x2="12.01" y2="17"></line>
								</svg>
							</div>
							<h3 class="eakc-category-title"><?php _e('FAQs', 'energy-alabama-kc'); ?></h3>
							<p class="eakc-category-description"><?php _e('Get answers to frequently asked questions about clean energy and energy efficiency.', 'energy-alabama-kc'); ?></p>
							<span class="eakc-category-count">
								<?php
								printf(
									_n('%s question', '%s questions', $total_faqs, 'energy-alabama-kc'),
									number_format_i18n($total_faqs)
								);
								?>
							</span>
						</a>
					</div>

					<?php
					// Add Glossary card as the last item
					$glossary_count = wp_count_posts('glossary');
					$total_definitions = $glossary_count->publish;
					?>
					<div class="eakc-category-card">
						<a href="<?php echo esc_url(home_url('/knowledge-center/glossary/')); ?>" class="eakc-category-link">
							<div class="eakc-category-icon">
								<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
									<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
									<circle cx="10" cy="8" r="2"></circle>
									<path d="M8 14s1.5-2 2-2 2 2 2 2"></path>
								</svg>
							</div>
							<h3 class="eakc-category-title"><?php _e('Glossary', 'energy-alabama-kc'); ?></h3>
							<p class="eakc-category-description"><?php _e('Find definitions for clean energy terms, policy concepts, and technical terminology.', 'energy-alabama-kc'); ?></p>
							<span class="eakc-category-count">
								<?php
								printf(
									_n('%s definition', '%s definitions', $total_definitions, 'energy-alabama-kc'),
									number_format_i18n($total_definitions)
								);
								?>
							</span>
						</a>
					</div>
				</div>
			<?php else : ?>
				<p class="eakc-no-categories">
					<?php _e('No categories found. Please add some knowledge center categories.', 'energy-alabama-kc'); ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<!-- Recent Articles Section -->
	<?php if (!empty($recent_articles)) : ?>
		<section class="eakc-recent-articles">
			<div class="eakc-container">
				<h2 class="eakc-section-title">
					<?php _e('Recent Articles', 'energy-alabama-kc'); ?>
				</h2>
				
				<div class="eakc-articles-grid">
					<?php
					foreach ($recent_articles as $article) :
						$article_link = get_permalink($article->ID);
						$excerpt = wp_trim_words($article->post_excerpt ?: $article->post_content, 20, '...');
						$categories = get_the_terms($article->ID, 'kc_category');
						$read_time = get_post_meta($article->ID, '_eakc_read_time', true);
						$featured_icon = get_post_meta($article->ID, '_eakc_featured_icon', true);
						$icon_color = get_post_meta($article->ID, '_eakc_icon_color', true) ?: '#ffffff';
					?>
						<article class="eakc-article-card">
							<div class="eakc-card-header">
								<?php if (has_post_thumbnail($article->ID)): ?>
									<div class="eakc-card-image">
										<a href="<?php echo esc_url($article_link); ?>">
											<?php echo get_the_post_thumbnail($article->ID, 'medium', array('loading' => 'lazy', 'alt' => esc_attr($article->post_title))); ?>
										</a>
									</div>
								<?php elseif ($featured_icon): ?>
									<a href="<?php echo esc_url($article_link); ?>" class="eakc-card-icon">
										<i class="<?php echo esc_attr($featured_icon); ?>" style="color: <?php echo esc_attr($icon_color); ?>; font-size: 48px;"></i>
									</a>
								<?php else: ?>
									<a href="<?php echo esc_url($article_link); ?>" class="eakc-card-icon">
										<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
											<path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
										</svg>
									</a>
								<?php endif; ?>
								
								<div class="eakc-card-meta">
									
									<?php if ($read_time): ?>
										<span class="eakc-read-time">
											<?php printf(__('%d min read', 'energy-alabama-kc'), $read_time); ?>
										</span>
									<?php endif; ?>
								</div>
							</div>
							
							<div class="eakc-card-content">
								<h3 class="eakc-card-title">
									<a href="<?php echo esc_url($article_link); ?>"><?php echo esc_html($article->post_title); ?></a>
								</h3>
								
								<?php if ($categories && !is_wp_error($categories)): ?>
									<div class="eakc-card-category">
										<a href="<?php echo esc_url(get_term_link($categories[0])); ?>" class="eakc-category-link">
											<?php echo esc_html($categories[0]->name); ?>
										</a>
									</div>
								<?php endif; ?>
								
								<div class="eakc-card-excerpt">
									<?php echo esc_html($excerpt); ?>
								</div>
								
								<div class="eakc-card-footer">
									<time class="eakc-card-date" datetime="<?php echo esc_attr(get_the_date('c', $article->ID)); ?>">
										<?php echo get_the_date('M j, Y', $article->ID); ?>
									</time>
									
									<a href="<?php echo esc_url($article_link); ?>" class="eakc-read-more">
										<?php _e('Read More', 'energy-alabama-kc'); ?>
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
											<line x1="7" y1="17" x2="17" y2="7"/>
											<polyline points="7,7 17,7 17,17"/>
										</svg>
									</a>
								</div>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
				
				<div class="eakc-view-all">
					<a href="<?php echo esc_url(get_post_type_archive_link('kc_article')); ?>" class="eakc-button eakc-button-secondary">
						<?php _e('View All Articles', 'energy-alabama-kc'); ?>
					</a>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Quick Links Section -->
	<section class="eakc-quick-links">
		<div class="eakc-container">
			<h2 class="eakc-section-title">
				<?php _e('Quick Links', 'energy-alabama-kc'); ?>
			</h2>
			
			<div class="eakc-quick-links-grid">
				<a href="<?php echo esc_url(get_term_link(get_term_by('slug', 'legal-regulatory', 'kc_category'))); ?>" class="eakc-quick-link">
					<div class="eakc-quick-link-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
							<polyline points="14,2 14,8 20,8"></polyline>
							<line x1="16" y1="13" x2="8" y2="13"></line>
							<line x1="16" y1="17" x2="8" y2="17"></line>
							<polyline points="10,9 9,9 8,9"></polyline>
						</svg>
					</div>
					<span><?php _e('Legal & Regulatory Documents', 'energy-alabama-kc'); ?></span>
				</a>
				
				<a href="<?php echo esc_url(get_post_type_archive_link('docket')); ?>" class="eakc-quick-link">
					<div class="eakc-quick-link-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
							<path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
						</svg>
					</div>
					<span><?php _e('Browse Dockets', 'energy-alabama-kc'); ?></span>
				</a>
				
				<a href="<?php echo esc_url(get_term_link(get_term_by('slug', 'educator-resources', 'kc_category'))); ?>" class="eakc-quick-link">
					<div class="eakc-quick-link-icon">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
							<path d="M6 12v5c3 3 9 3 12 0v-5"></path>
						</svg>
					</div>
					<span><?php _e('Educator Resources', 'energy-alabama-kc'); ?></span>
				</a>
			</div>
		</div>
	</section>

</div>

<?php get_footer(); ?>
