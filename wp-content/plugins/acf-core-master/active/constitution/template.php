<?php
/**
 * Block Name: U.S. Constitution Block
 *
 * Displays the complete U.S. Constitution with integrated navigation
 */

// Create ID attribute
$id = $block['id'];
if( !empty($block['anchor']) ) {
	$id = $block['anchor'];
}

// Create class attribute
$className = 'constitution-block';
if( !empty($block['className']) ) {
	$className .= ' ' . $block['className'];
}
if( !empty($block['align']) ) {
	$className .= ' align' . $block['align'];
}

// Get field values
$display_style = get_field('display_style') ?: 'full';
$show_toc = get_field('show_toc');
$show_navigation = get_field('show_navigation');
$nav_position = get_field('nav_position') ?: 'right';
$show_progress = get_field('show_progress');
$nav_collapsible = get_field('nav_collapsible');
$color_theme = get_field('color_theme') ?: 'classic';
$show_source = get_field('show_source');

// Add theme class
$className .= ' constitution-theme--' . $color_theme;

// Include the Constitution content
require_once plugin_dir_path( __FILE__ ) . 'constitution-content.php';
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>" data-block-id="<?php echo esc_attr($id); ?>">
	
	<?php if ($show_progress): ?>
		<div class="constitution-progress">
			<div class="constitution-progress__bar" id="reading-progress-<?php echo esc_attr($id); ?>"></div>
		</div>
	<?php endif; ?>

	<?php if ($show_navigation): ?>
		<nav class="constitution-nav constitution-nav--<?php echo esc_attr($nav_position); ?>"
			 id="constitution-nav-<?php echo esc_attr($id); ?>">

			<ul class="constitution-nav__list">
				<li class="constitution-nav__item">
					<a href="#preamble-<?php echo esc_attr($id); ?>" 
					   class="constitution-nav__link" 
					   data-section="preamble">
						<span class="constitution-nav__dot"></span>
						<span class="constitution-nav__label">Preamble</span>
					</a>
				</li>
				
				<?php if ($display_style === 'full' || $display_style === 'articles'): ?>
					<?php for ($i = 1; $i <= 7; $i++): ?>
						<li class="constitution-nav__item">
							<a href="#article-<?php echo $i; ?>-<?php echo esc_attr($id); ?>" 
							   class="constitution-nav__link" 
							   data-section="article-<?php echo $i; ?>">
								<span class="constitution-nav__dot"></span>
								<span class="constitution-nav__label">Article <?php echo $i; ?></span>
							</a>
						</li>
					<?php endfor; ?>
				<?php endif; ?>
				
				<?php if ($display_style === 'full' || $display_style === 'amendments'): ?>
					<li class="constitution-nav__item constitution-nav__item--separator">
						<a href="#amendments-<?php echo esc_attr($id); ?>" 
						   class="constitution-nav__link" 
						   data-section="amendments">
							<span class="constitution-nav__dot"></span>
							<span class="constitution-nav__label">Amendments</span>
						</a>
					</li>
				<?php endif; ?>
			</ul>
		</nav>
	<?php endif; ?>
	
	<?php if ($show_toc): ?>
		<nav class="constitution-toc">
			<h2>Table of Contents</h2>
			<ul>
				<li><a href="#preamble-<?php echo esc_attr($id); ?>">Preamble</a></li>
				<?php if ($display_style === 'full' || $display_style === 'articles'): ?>
					<li><a href="#articles-<?php echo esc_attr($id); ?>">Articles I-VII</a>
						<ul>
							<li><a href="#article-1-<?php echo esc_attr($id); ?>">Article I - Legislative Branch</a></li>
							<li><a href="#article-2-<?php echo esc_attr($id); ?>">Article II - Executive Branch</a></li>
							<li><a href="#article-3-<?php echo esc_attr($id); ?>">Article III - Judicial Branch</a></li>
							<li><a href="#article-4-<?php echo esc_attr($id); ?>">Article IV - States' Relations</a></li>
							<li><a href="#article-5-<?php echo esc_attr($id); ?>">Article V - Amendment Process</a></li>
							<li><a href="#article-6-<?php echo esc_attr($id); ?>">Article VI - Supreme Law</a></li>
							<li><a href="#article-7-<?php echo esc_attr($id); ?>">Article VII - Ratification</a></li>
						</ul>
					</li>
				<?php endif; ?>
				<?php if ($display_style === 'full' || $display_style === 'amendments'): ?>
					<li><a href="#amendments-<?php echo esc_attr($id); ?>">Amendments 1-27</a></li>
				<?php endif; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<div class="constitution-content">
		
		<header class="constitution-header">
			<h1>The Constitution of the United States</h1>
		</header>

		<!-- Preamble -->
		<section id="preamble-<?php echo esc_attr($id); ?>" class="constitution-section preamble" data-section="preamble">
			<h2>Preamble</h2>
			<?php echo render_preamble(); ?>
		</section>

		<!-- Articles -->
		<?php if ($display_style === 'full' || $display_style === 'articles'): ?>
			<section id="articles-<?php echo esc_attr($id); ?>" class="constitution-section articles">
				<?php echo render_articles($display_style === 'collapsible', $id); ?>
			</section>
		<?php endif; ?>

		<!-- Amendments -->
		<?php if ($display_style === 'full' || $display_style === 'amendments'): ?>
			<section id="amendments-<?php echo esc_attr($id); ?>" class="constitution-section amendments" data-section="amendments">
				<h2>Amendments to the Constitution</h2>
				<?php echo render_amendments($display_style === 'collapsible', $id); ?>
			</section>
		<?php endif; ?>

		<?php if ($show_source): ?>
			<footer class="constitution-source">
				<p><em>Source: <a href="https://www.archives.gov/founding-docs/constitution-transcript" target="_blank" rel="noopener">National Archives</a></em></p>
			</footer>
		<?php endif; ?>

	</div>

</div>