=== Custom Styles ===
Contributors: you
Tags: css, styles, global, theme, site
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.0.0
License: GPLv2 or later

A tiny plugin that enqueues a single site-wide stylesheet. Edit `assets/css/custom-styles.css` to add your global CSS. It also loads in the block editor so you can see styles while editing.

== Installation ==
1. Download the ZIP and upload it to **Plugins → Add New → Upload Plugin**, then Activate.
2. Edit `wp-content/plugins/custom-styles/assets/css/custom-styles.css` and add your CSS.
3. Clear caches if you use a caching plugin or CDN (the plugin busts cache via filemtime, but CDNs may need a purge).

== Frequently Asked ==
= Will this override my theme? =
It loads with a later priority than most themes (priority 20). If something still doesn't apply, increase specificity or use `!important` sparingly.

= Will it work with the Site Editor (FSE)? =
Yes. It enqueues on the front end and in the block editor.

== Changelog ==
= 1.0.0 =
* First release.
