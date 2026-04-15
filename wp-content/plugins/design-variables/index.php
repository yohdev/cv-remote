<?php
/**
 * Plugin Name: Design Variables Reference
 * Description: Displays color, typography, and button CSS variables from your FSE theme for easy reference.
 * Version:     2.0
 * Author:      Doc Hazzard
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Design_Variables_Reference {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_styles' ] );
	}

	public function add_admin_page() {
		add_theme_page(
			'Design Variables',
			'Design Variables',
			'edit_theme_options',
			'design-variables-reference',
			[ $this, 'render_admin_page' ]
		);
	}

	public function enqueue_admin_styles( $hook ) {
		if ( 'appearance_page_design-variables-reference' !== $hook ) {
			return;
		}
		wp_add_inline_style( 'wp-admin', $this->get_admin_css() );
	}

	// ── Data Retrieval ──────────────────────────────────────────

	public function get_custom_colors() {
		$colors = [];

		if ( function_exists( 'wp_get_global_settings' ) ) {
			$settings = wp_get_global_settings();

			if ( isset( $settings['color']['palette']['custom'] ) ) {
				$colors = array_merge( $colors, $settings['color']['palette']['custom'] );
			}
			if ( isset( $settings['color']['palette']['theme'] ) ) {
				$colors = array_merge( $colors, $settings['color']['palette']['theme'] );
			}
		}

		if ( empty( $colors ) ) {
			$theme_json_path = get_stylesheet_directory() . '/theme.json';
			if ( file_exists( $theme_json_path ) ) {
				$theme_json = json_decode( file_get_contents( $theme_json_path ), true );
				if ( isset( $theme_json['settings']['color']['palette'] ) ) {
					$colors = $theme_json['settings']['color']['palette'];
				}
			}
		}

		return $colors;
	}

	public function get_font_families() {
		$fonts = [];

		if ( function_exists( 'wp_get_global_settings' ) ) {
			$settings = wp_get_global_settings();

			if ( isset( $settings['typography']['fontFamilies']['custom'] ) ) {
				$fonts = array_merge( $fonts, $settings['typography']['fontFamilies']['custom'] );
			}
			if ( isset( $settings['typography']['fontFamilies']['theme'] ) ) {
				$fonts = array_merge( $fonts, $settings['typography']['fontFamilies']['theme'] );
			}
		}

		if ( empty( $fonts ) ) {
			$theme_json_path = get_stylesheet_directory() . '/theme.json';
			if ( file_exists( $theme_json_path ) ) {
				$theme_json = json_decode( file_get_contents( $theme_json_path ), true );
				if ( isset( $theme_json['settings']['typography']['fontFamilies'] ) ) {
					$fonts = $theme_json['settings']['typography']['fontFamilies'];
				}
			}
		}

		return $fonts;
	}

	public function get_font_sizes() {
		$sizes = [];

		if ( function_exists( 'wp_get_global_settings' ) ) {
			$settings = wp_get_global_settings();

			if ( isset( $settings['typography']['fontSizes']['custom'] ) ) {
				$sizes = array_merge( $sizes, $settings['typography']['fontSizes']['custom'] );
			}
			if ( isset( $settings['typography']['fontSizes']['theme'] ) ) {
				$sizes = array_merge( $sizes, $settings['typography']['fontSizes']['theme'] );
			}
		}

		if ( empty( $sizes ) ) {
			$theme_json_path = get_stylesheet_directory() . '/theme.json';
			if ( file_exists( $theme_json_path ) ) {
				$theme_json = json_decode( file_get_contents( $theme_json_path ), true );
				if ( isset( $theme_json['settings']['typography']['fontSizes'] ) ) {
					$sizes = $theme_json['settings']['typography']['fontSizes'];
				}
			}
		}

		return $sizes;
	}

	public function get_button_styles() {
		$button = [];

		if ( function_exists( 'wp_get_global_styles' ) ) {
			$styles = wp_get_global_styles();

			// Element-level button styles
			if ( isset( $styles['elements']['button'] ) ) {
				$button['element'] = $styles['elements']['button'];
			}

			// Block-level button styles (core/button)
			if ( isset( $styles['blocks']['core/button'] ) ) {
				$button['block'] = $styles['blocks']['core/button'];
			}
		}

		// Fallback to theme.json
		if ( empty( $button ) ) {
			$theme_json_path = get_stylesheet_directory() . '/theme.json';
			if ( file_exists( $theme_json_path ) ) {
				$theme_json = json_decode( file_get_contents( $theme_json_path ), true );
				if ( isset( $theme_json['styles']['elements']['button'] ) ) {
					$button['element'] = $theme_json['styles']['elements']['button'];
				}
				if ( isset( $theme_json['styles']['blocks']['core/button'] ) ) {
					$button['block'] = $theme_json['styles']['blocks']['core/button'];
				}
			}
		}

		return $button;
	}

	// ── Helpers ──────────────────────────────────────────────────

	/**
	 * Convert theme.json ref strings like "var:preset|color|contrast" to CSS var().
	 */
	private function resolve_theme_ref( $value ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}
		if ( strpos( $value, 'var:' ) === 0 ) {
			$parts = explode( '|', substr( $value, 4 ) );
			return 'var(--wp--' . implode( '--', $parts ) . ')';
		}
		return $value;
	}

	/**
	 * Recursively walk a styles array and collect key → resolved value pairs.
	 */
	private function flatten_button_props( $data, $prefix = '' ) {
		$rows = [];
		if ( ! is_array( $data ) ) {
			return $rows;
		}
		foreach ( $data as $key => $value ) {
			$label = $prefix ? $prefix . ' → ' . $key : $key;
			if ( is_array( $value ) ) {
				$rows = array_merge( $rows, $this->flatten_button_props( $value, $label ) );
			} else {
				$rows[] = [
					'property' => $label,
					'raw'      => $value,
					'resolved' => $this->resolve_theme_ref( $value ),
				];
			}
		}
		return $rows;
	}

	// ── Render ───────────────────────────────────────────────────

	public function render_admin_page() {
		$colors  = $this->get_custom_colors();
		$fonts   = $this->get_font_families();
		$sizes   = $this->get_font_sizes();
		$button  = $this->get_button_styles();
		?>
		<div class="wrap dvr-wrap">
			<h1>Design Variables Reference</h1>
			<p>Click any <code>code</code> value to copy it to your clipboard.</p>

			<!-- Tab Navigation -->
			<nav class="nav-tab-wrapper dvr-tabs">
				<a href="#dvr-colors" class="nav-tab nav-tab-active" data-tab="dvr-colors">Colors</a>
				<a href="#dvr-typography" class="nav-tab" data-tab="dvr-typography">Typography</a>
				<a href="#dvr-buttons" class="nav-tab" data-tab="dvr-buttons">Button Styles</a>
			</nav>

			<!-- ── COLORS ────────────────────────────────────────── -->
			<div id="dvr-colors" class="dvr-tab-panel dvr-tab-panel--active">
				<?php if ( empty( $colors ) ) : ?>
					<div class="notice notice-warning"><p>No custom colors found.</p></div>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped dvr-table">
						<thead>
							<tr>
								<th class="dvr-swatch-col">Swatch</th>
								<th>Name</th>
								<th>Hex Value</th>
								<th>CSS Variable</th>
								<th>Usage Example</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $colors as $color ) :
								$slug    = isset( $color['slug'] ) ? $color['slug'] : sanitize_title( $color['name'] );
								$css_var = '--wp--preset--color--' . $slug;
								$hex     = isset( $color['color'] ) ? $color['color'] : '#000000';
								$name    = isset( $color['name'] ) ? $color['name'] : $slug;
							?>
								<tr>
									<td class="dvr-swatch-col">
										<span class="dvr-swatch" style="background-color: <?php echo esc_attr( $hex ); ?>;"></span>
									</td>
									<td><?php echo esc_html( $name ); ?></td>
									<td>
										<code class="dvr-copyable" data-copy="<?php echo esc_attr( $hex ); ?>">
											<?php echo esc_html( $hex ); ?>
										</code>
									</td>
									<td>
										<code class="dvr-copyable" data-copy="var(<?php echo esc_attr( $css_var ); ?>)">
											var(<?php echo esc_html( $css_var ); ?>)
										</code>
									</td>
									<td>
										<code class="dvr-copyable dvr-example" data-copy="color: var(<?php echo esc_attr( $css_var ); ?>);">
											color: var(<?php echo esc_html( $css_var ); ?>);
										</code>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div class="dvr-additional-info">
						<h2>Quick Reference</h2>
						<h3>Background Colors</h3>
						<div class="dvr-code-block">
							<pre><?php
							foreach ( $colors as $color ) :
								$slug = isset( $color['slug'] ) ? $color['slug'] : sanitize_title( $color['name'] );
								echo '.has-' . esc_html( $slug ) . '-background-color' . "\n";
							endforeach;
							?></pre>
						</div>
						<h3>Text Colors</h3>
						<div class="dvr-code-block">
							<pre><?php
							foreach ( $colors as $color ) :
								$slug = isset( $color['slug'] ) ? $color['slug'] : sanitize_title( $color['name'] );
								echo '.has-' . esc_html( $slug ) . '-color' . "\n";
							endforeach;
							?></pre>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- ── TYPOGRAPHY ────────────────────────────────────── -->
			<div id="dvr-typography" class="dvr-tab-panel">

				<h2>Font Families</h2>
				<?php if ( empty( $fonts ) ) : ?>
					<div class="notice notice-warning"><p>No font families found in your theme settings.</p></div>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped dvr-table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Preview</th>
								<th>CSS Variable</th>
								<th>Font Stack</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $fonts as $font ) :
								$slug     = isset( $font['slug'] ) ? $font['slug'] : sanitize_title( $font['name'] );
								$css_var  = '--wp--preset--font-family--' . $slug;
								$stack    = isset( $font['fontFamily'] ) ? $font['fontFamily'] : $slug;
								$name     = isset( $font['name'] ) ? $font['name'] : $slug;
							?>
								<tr>
									<td><strong><?php echo esc_html( $name ); ?></strong></td>
									<td>
										<span class="dvr-font-preview" style="font-family: <?php echo esc_attr( $stack ); ?>;">
											The quick brown fox jumps over the lazy dog
										</span>
									</td>
									<td>
										<code class="dvr-copyable" data-copy="var(<?php echo esc_attr( $css_var ); ?>)">
											var(<?php echo esc_html( $css_var ); ?>)
										</code>
									</td>
									<td>
										<code class="dvr-copyable" data-copy="<?php echo esc_attr( $stack ); ?>">
											<?php echo esc_html( $stack ); ?>
										</code>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div class="dvr-additional-info" style="margin-top:20px;">
						<h3>Font Family CSS Classes</h3>
						<div class="dvr-code-block">
							<pre><?php
							foreach ( $fonts as $font ) :
								$slug = isset( $font['slug'] ) ? $font['slug'] : sanitize_title( $font['name'] );
								echo '.has-' . esc_html( $slug ) . '-font-family' . "\n";
							endforeach;
							?></pre>
						</div>
					</div>
				<?php endif; ?>

				<h2 style="margin-top:30px;">Font Sizes</h2>
				<?php if ( empty( $sizes ) ) : ?>
					<div class="notice notice-warning"><p>No font size presets found.</p></div>
				<?php else : ?>
					<table class="wp-list-table widefat fixed striped dvr-table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Preview</th>
								<th>Size Value</th>
								<th>CSS Variable</th>
								<th>Fluid Range</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $sizes as $size ) :
								$slug    = isset( $size['slug'] ) ? $size['slug'] : sanitize_title( $size['name'] );
								$css_var = '--wp--preset--font-size--' . $slug;
								$val     = isset( $size['size'] ) ? $size['size'] : '';
								$name    = isset( $size['name'] ) ? $size['name'] : $slug;

								$fluid_info = '';
								if ( isset( $size['fluid'] ) && is_array( $size['fluid'] ) ) {
									$min = isset( $size['fluid']['min'] ) ? $size['fluid']['min'] : '?';
									$max = isset( $size['fluid']['max'] ) ? $size['fluid']['max'] : '?';
									$fluid_info = $min . ' → ' . $max;
								} elseif ( isset( $size['fluid'] ) && $size['fluid'] === false ) {
									$fluid_info = 'Fixed';
								}
							?>
								<tr>
									<td><strong><?php echo esc_html( $name ); ?></strong></td>
									<td>
										<span style="font-size: <?php echo esc_attr( $val ); ?>;">Aa</span>
									</td>
									<td>
										<code class="dvr-copyable" data-copy="<?php echo esc_attr( $val ); ?>">
											<?php echo esc_html( $val ); ?>
										</code>
									</td>
									<td>
										<code class="dvr-copyable" data-copy="var(<?php echo esc_attr( $css_var ); ?>)">
											var(<?php echo esc_html( $css_var ); ?>)
										</code>
									</td>
									<td><?php echo esc_html( $fluid_info ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div class="dvr-additional-info" style="margin-top:20px;">
						<h3>Font Size CSS Classes</h3>
						<div class="dvr-code-block">
							<pre><?php
							foreach ( $sizes as $size ) :
								$slug = isset( $size['slug'] ) ? $size['slug'] : sanitize_title( $size['name'] );
								echo '.has-' . esc_html( $slug ) . '-font-size' . "\n";
							endforeach;
							?></pre>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- ── BUTTON STYLES ─────────────────────────────────── -->
			<div id="dvr-buttons" class="dvr-tab-panel">

				<?php if ( empty( $button ) ) : ?>
					<div class="notice notice-warning"><p>No button styles found in your theme configuration.</p></div>
				<?php else : ?>

					<?php
					// Merge element + block button data, element takes priority for display
					$btn_source = isset( $button['element'] ) ? $button['element'] : ( isset( $button['block'] ) ? $button['block'] : [] );
					$btn_rows   = $this->flatten_button_props( $btn_source );
					?>

					<h2>Button Element Styles</h2>
					<p>These styles apply to <code>&lt;button&gt;</code>, <code>.wp-block-button__link</code>, and <code>.wp-element-button</code> elements.</p>

					<div class="dvr-button-preview-wrap">
						<h3>Live Preview</h3>
						<div class="dvr-button-preview">
							<a class="wp-element-button" href="#">Default Button</a>
						</div>
					</div>

					<table class="wp-list-table widefat fixed striped dvr-table">
						<thead>
							<tr>
								<th>Property</th>
								<th>Raw Value (theme.json)</th>
								<th>Resolved CSS</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $btn_rows as $row ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $row['property'] ); ?></strong></td>
									<td>
										<code class="dvr-copyable" data-copy="<?php echo esc_attr( $row['raw'] ); ?>">
											<?php echo esc_html( $row['raw'] ); ?>
										</code>
									</td>
									<td>
										<code class="dvr-copyable" data-copy="<?php echo esc_attr( $row['resolved'] ); ?>">
											<?php echo esc_html( $row['resolved'] ); ?>
										</code>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php if ( isset( $button['block'] ) && isset( $button['block']['variations'] ) ) : ?>
						<h2 style="margin-top:30px;">Button Variations</h2>
						<?php foreach ( $button['block']['variations'] as $var_name => $var_data ) :
							$var_rows = $this->flatten_button_props( $var_data );
						?>
							<h3><?php echo esc_html( ucfirst( $var_name ) ); ?></h3>
							<table class="wp-list-table widefat fixed striped dvr-table">
								<thead>
									<tr>
										<th>Property</th>
										<th>Raw Value</th>
										<th>Resolved CSS</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $var_rows as $row ) : ?>
										<tr>
											<td><strong><?php echo esc_html( $row['property'] ); ?></strong></td>
											<td>
												<code class="dvr-copyable" data-copy="<?php echo esc_attr( $row['raw'] ); ?>">
													<?php echo esc_html( $row['raw'] ); ?>
												</code>
											</td>
											<td>
												<code class="dvr-copyable" data-copy="<?php echo esc_attr( $row['resolved'] ); ?>">
													<?php echo esc_html( $row['resolved'] ); ?>
												</code>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						<?php endforeach; ?>
					<?php endif; ?>

					<div class="dvr-additional-info" style="margin-top:30px;">
						<h2>Gravity Forms Override Reference</h2>
						<p>Use these selectors to override Gravity Forms button styles with your theme variables:</p>
						<div class="dvr-code-block">
							<pre><?php echo esc_html(
'/* ── Gravity Forms Button Override ── */
.gform_wrapper input[type="submit"],
.gform_wrapper button[type="submit"],
.gform_wrapper .gform_button,
.gform_wrapper .gform_next_button,
.gform_wrapper .gform_previous_button {
    background-color: var(--wp--preset--color--contrast);
    color: var(--wp--preset--color--base);
    font-size: var(--wp--preset--font-size--medium);
    font-family: inherit;
    padding: 1rem 2.25rem;
    border: none;
    border-radius: 0;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
}

.gform_wrapper input[type="submit"]:hover,
.gform_wrapper button[type="submit"]:hover,
.gform_wrapper .gform_button:hover {
    opacity: 0.85;
}' ); ?></pre>
						</div>

						<h3>Key CSS Selectors</h3>
						<div class="dvr-code-block">
							<pre><?php echo esc_html(
'/* WordPress block button */
.wp-block-button__link
.wp-element-button
button

/* Gravity Forms buttons */
.gform_wrapper input[type="submit"]
.gform_wrapper button[type="submit"]
.gform_wrapper .gform_button
.gform_wrapper .gform_next_button
.gform_wrapper .gform_previous_button' ); ?></pre>
						</div>
					</div>

				<?php endif; ?>
			</div>
		</div>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Copy to clipboard
			document.querySelectorAll('.dvr-copyable').forEach(function(el) {
				el.addEventListener('click', function() {
					var text = this.getAttribute('data-copy');
					navigator.clipboard.writeText(text).then(function() {
						el.classList.add('dvr-copied');
						setTimeout(function() { el.classList.remove('dvr-copied'); }, 1000);
					});
				});
			});

			// Tab navigation
			document.querySelectorAll('.dvr-tabs .nav-tab').forEach(function(tab) {
				tab.addEventListener('click', function(e) {
					e.preventDefault();
					document.querySelectorAll('.dvr-tabs .nav-tab').forEach(function(t) { t.classList.remove('nav-tab-active'); });
					document.querySelectorAll('.dvr-tab-panel').forEach(function(p) { p.classList.remove('dvr-tab-panel--active'); });
					this.classList.add('nav-tab-active');
					document.getElementById(this.getAttribute('data-tab')).classList.add('dvr-tab-panel--active');
				});
			});
		});
		</script>
		<?php
	}

	// ── Admin CSS ───────────────────────────────────────────────

	public function get_admin_css() {
		return '
			.dvr-wrap { max-width: 1200px; }
			.dvr-table { margin-top: 20px; }

			/* Tabs */
			.dvr-tab-panel { display: none; margin-top: 20px; }
			.dvr-tab-panel--active { display: block; }

			/* Swatch */
			.dvr-swatch-col { width: 60px; }
			.dvr-swatch {
				display: inline-block; width: 36px; height: 36px;
				border-radius: 4px; border: 1px solid rgba(0,0,0,0.1);
				box-shadow: inset 0 0 0 1px rgba(255,255,255,0.2);
			}

			/* Copyable */
			.dvr-copyable {
				cursor: pointer; padding: 4px 8px; background: #f0f0f1;
				border-radius: 3px; transition: all 0.2s ease; display: inline-block;
			}
			.dvr-copyable:hover { background: #dcdcde; }
			.dvr-copied { background: #00a32a !important; color: #fff !important; }
			.dvr-example { font-size: 12px; }

			/* Additional Info */
			.dvr-additional-info {
				margin-top: 40px; padding: 20px; background: #fff;
				border: 1px solid #c3c4c7; border-radius: 4px;
			}
			.dvr-additional-info h2 { margin-top: 0; }
			.dvr-additional-info h3 { margin-bottom: 10px; }
			.dvr-code-block {
				background: #23282d; padding: 15px; border-radius: 4px; margin-bottom: 20px;
			}
			.dvr-code-block pre {
				color: #fff; margin: 0; white-space: pre-wrap;
				font-family: Consolas, Monaco, monospace; font-size: 13px;
			}

			/* Font preview */
			.dvr-font-preview { font-size: 16px; line-height: 1.4; }

			/* Button preview */
			.dvr-button-preview-wrap {
				margin: 20px 0; padding: 20px; background: #fff;
				border: 1px solid #c3c4c7; border-radius: 4px;
			}
			.dvr-button-preview-wrap h3 { margin-top: 0; }
			.dvr-button-preview { padding: 20px; background: #f6f7f7; border-radius: 4px; text-align: center; }
		';
	}
}

new Design_Variables_Reference();
