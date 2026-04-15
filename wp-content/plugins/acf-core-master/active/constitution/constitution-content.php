<?php
/**
 * Constitution Content Functions
 */

function render_preamble() {
	return '<p class="preamble-text">We the People of the United States, in Order to form a more perfect Union, establish Justice, insure domestic Tranquility, provide for the common defence, promote the general Welfare, and secure the Blessings of Liberty to ourselves and our Posterity, do ordain and establish this Constitution for the United States of America.</p>';
}

function render_articles($collapsible = false, $block_id = '') {
	$articles = get_constitution_articles();
	$output = '';
	
	foreach ($articles as $article) {
		$article_id = 'article-' . $article['number'] . '-' . $block_id;
		
		if ($collapsible) {
			$output .= '<details class="article-collapsible" data-section="article-' . $article['number'] . '">';
			$output .= '<summary id="' . esc_attr($article_id) . '"><h3>' . esc_html($article['title']) . '</h3></summary>';
			$output .= '<div class="article-content">' . $article['content'] . '</div>';
			$output .= '</details>';
		} else {
			$output .= '<article id="' . esc_attr($article_id) . '" class="constitution-article" data-section="article-' . $article['number'] . '">';
			$output .= '<h3>' . esc_html($article['title']) . '</h3>';
			$output .= '<div class="article-content">' . $article['content'] . '</div>';
			$output .= '</article>';
		}
	}
	
	return $output;
}

function render_amendments($collapsible = false, $block_id = '') {
	$amendments = get_constitution_amendments();
	$output = '';
	
	foreach ($amendments as $amendment) {
		$amendment_id = 'amendment-' . $amendment['number'] . '-' . $block_id;
		
		if ($collapsible) {
			$output .= '<details class="amendment-collapsible" data-section="amendment-' . $amendment['number'] . '">';
			$output .= '<summary id="' . esc_attr($amendment_id) . '"><h4>' . esc_html($amendment['title']) . '</h4></summary>';
			$output .= '<div class="amendment-content">' . $amendment['content'] . '</div>';
			$output .= '</details>';
		} else {
			$output .= '<article id="' . esc_attr($amendment_id) . '" class="constitution-amendment" data-section="amendment-' . $amendment['number'] . '">';
			$output .= '<h4>' . esc_html($amendment['title']) . '</h4>';
			$output .= '<div class="amendment-content">' . $amendment['content'] . '</div>';
			$output .= '</article>';
		}
	}
	
	return $output;
}

function get_constitution_articles() {
	require_once plugin_dir_path( __FILE__ ) . 'data/articles.php';
	return get_articles_data();
}

function get_constitution_amendments() {
	require_once plugin_dir_path( __FILE__ ) . 'data/amendments.php';
	return get_amendments_data();
}