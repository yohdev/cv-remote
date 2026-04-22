<?php
/**
 * Block Name: Featured Insights
 */

$id = $block['id'];
if (!empty($block['anchor'])) {
	$id = $block['anchor'];
}

$className = 'featured-insights';
if (!empty($block['className'])) {
	$className .= ' ' . $block['className'];
}

$posts = get_field('featured_posts');
if (empty($posts)) return;
?>

<div class="<?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
	<div class="featured-insights__grid">
		<?php foreach ($posts as $post) : setup_postdata($post); ?>
			<?php
			$title     = get_the_title($post);
			$permalink = get_permalink($post);
			$excerpt   = wp_trim_words(get_the_excerpt($post), 30, '...');
			$date      = get_the_date('F j, Y', $post);
			$author    = get_the_author_meta('display_name', $post->post_author);
			$terms     = get_the_terms($post->ID, 'category');
			?>
			<article class="featured-insights__card">
				<div class="featured-insights__card-body">
					<h3 class="featured-insights__title">
						<a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
					</h3>
					<p class="featured-insights__meta">
						<?php echo esc_html($date); ?>
					</p>
					<?php if ($excerpt) : ?>
						<p class="featured-insights__excerpt"><?php echo esc_html($excerpt); ?></p>
					<?php endif; ?>
					<?php if ($terms && !is_wp_error($terms)) : ?>
						<div class="featured-insights__tags">
							<?php foreach ($terms as $term) : ?>
								<span class="featured-insights__tag"><?php echo esc_html($term->name); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
				<a class="featured-insights__readmore" href="<?php echo esc_url($permalink); ?>">
					Read more <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 16L6.575 14.6L12.175 9H0V7H12.175L6.575 1.4L8 0L16 8L8 16Z" fill="#00508D"></path></svg>
				</a>
			</article>
		<?php endforeach; wp_reset_postdata(); ?>
	</div>
</div>