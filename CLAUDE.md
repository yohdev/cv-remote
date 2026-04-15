# CVRemote — AI Agent Instructions

This is a WordPress site for **CVRemote** (cardiovascular remote monitoring), managed locally with [WordPress Studio](https://developer.wordpress.com/studio/). For Studio-specific CLI commands and constraints, see [STUDIO.md](STUDIO.md). For agent coding conventions, see [AGENTS.md](AGENTS.md).

## Architecture Overview

This site uses a **plugin-first architecture**. The active theme (`ultra-empty-fse`) is intentionally minimal — it provides layout dimensions, a base color palette, and heading sizes via `theme.json`, but contains no templates, styles, or logic of its own. All site functionality is delivered through custom plugins.

**Do not add features to the theme.** Extend through the custom plugins or create new ones.

### Tech Stack

- **WordPress** running on Studio (PHP WASM + SQLite, not MySQL)
- **Theme:** `ultra-empty-fse` — FSE block theme, layout-only shell
- **Custom blocks:** ACF (Advanced Custom Fields) registered via `acf-core-master` plugin
- **Styling:** Plugin-enqueued CSS (`custom-styles`, per-block stylesheets), no CSS framework
- **JS:** Vanilla JS, jQuery where ACF blocks need it, no build step

## What's Custom vs. Third-Party

Only **6 plugins** in this repo are custom code. Everything else is a third-party dependency managed via `.gitignore`.

### Custom Plugins (our code — edit freely)

| Plugin | Path | Purpose |
|--------|------|---------|
| **custom-functions** | `wp-content/plugins/custom-functions/` | Site-wide logic: CPTs (testimonial, careers), taxonomies (role, career_category), GTM/Google Ads tracking, color palette injection, navigation, featured image fallback |
| **custom-scripts** | `wp-content/plugins/custom-scripts/` | Frontend JS and CSS for animations and site scripting |
| **custom-styles** | `wp-content/plugins/custom-styles/` | Global stylesheet with filemtime cache-busting, loads on frontend + block editor |
| **design-variables** | `wp-content/plugins/design-variables/` | Admin reference page showing theme colors, typography, and button styles from theme.json |
| **custom-front-admin-bar-master** | `wp-content/plugins/custom-front-admin-bar-master/` | Replaces WP admin bar with a minimal hover-triggered footer toolbar (admin/editor only) |
| **acf-core-master** | `wp-content/plugins/acf-core-master/` | ACF block registration system — auto-loads block modules from `active/` directory |

### Third-Party Plugins (do not edit, managed externally)

advanced-custom-fields-pro, gravityforms, gravityformsmailgun, gravitysmtp, integration-for-gravity-forms-and-pipedrive, safe-svg, wordpress-importer, wordpress-seo, wp-migrate-db, wpe-site-migration, akismet

## Custom Post Types & Taxonomies

| Type | Slug | Registered In | Notes |
|------|------|--------------|-------|
| Testimonial | `testimonial` | custom-functions | Has meta: `testimonial_company`, `testimonial_position`. Used by testimonial-card, testimonials-carousel, large-testimonial-slider blocks |
| Careers | `careers` | custom-functions | Has taxonomy: `career_category`. Used by careers-block |
| Role | `role` (taxonomy) | custom-functions | On posts. Filters blog via insights-filter block. Default terms: Physician, Administrator, Device Clinician |

## ACF Block System (acf-core-master)

This is the most important plugin to understand. It auto-discovers and registers ACF blocks.

### How It Works

1. `acf-core.php` hooks into `acf/init`
2. It scans `active/` for subdirectories containing `module.php`
3. Each `module.php` calls `acf_register_block()` and loads its own `acf.json` field definitions
4. Blocks appear under the "ACF Core Blocks" category in the editor

### Block Module Convention

Every block follows this structure:

```
acf-core-master/active/{block-name}/
├── module.php        # Block registration + render callback
├── template.php      # HTML output (included by render callback)
├── acf.json          # ACF field group definition (loaded locally)
├── css/              # Block-specific styles
│   └── {name}.css
└── js/               # Block-specific scripts (optional)
    └── {name}.js
```

**To create a new block:** Create a folder in `active/`, add `module.php` following the pattern of existing blocks, include an `acf.json` for field definitions, and add `template.php` for rendering. The auto-loader will pick it up.

**To deactivate a block:** Move its folder from `active/` to `inactive/`.

### Active Blocks

| Block | Slug | Description |
|-------|------|-------------|
| Accordion | `accordion` | Collapsible content sections |
| Audio Player | `audio_player` | Plyr.js-based audio player |
| Careers Block | `careers-block` | Grid of career postings (uses careers CPT) |
| Constitution | `constitution_block` | U.S. Constitution display with navigation |
| Copy Link | `copy-permalink` | Button that copies post permalink to clipboard |
| Dot Navigation | `navigation_dots` | Fixed vertical dot nav for page sections |
| FAQ | `faq` | Expandable Q&A pairs |
| Gallery | `gallery_block` | Slideshow with Fancybox lightbox |
| Guest Notice | `guest-notice` | Content visible only to logged-out users |
| Image Uploader | `image_upload` | General-purpose image upload |
| Insights Carousel | `insights-carousel` | Latest posts grid/carousel (desktop/mobile) |
| Insights Filter | `insights-filter` | Blog filter bar with role taxonomy + search |
| Large Testimonial Slider | `large-testimonial-slider` | Peek-style testimonial carousel |
| Linked Content | `linked-content` | Wraps inner blocks in a clickable link |
| Menu | `menu` | Responsive nav with hamburger, loads WP menus dynamically |
| Slider Nav | `slider_nav_block` | Slide-out navigation drawer |
| Team Members | `team-members` | Team grid with photo + bio modals |
| Testimonial Card | `testimonial-card` | Single testimonial display |
| Testimonials Carousel | `testimonials-carousel` | Multi-item testimonial carousel |
| Timeline | `timeline` | Vertical/horizontal timeline |
| Video Button | `video_button` | Modal video trigger button |

### Inactive Blocks

Blocks in `inactive/` are deactivated but preserved. Check this directory before creating a new block — what you need may already exist.

## Color System

Colors are managed in two layers:

1. **theme.json** defines base palette: primary (#0043ce), secondary (#393939), tertiary (#f0f0f0), contrast (#111111), base (#ffffff), base-2 (#f7f7f7), accent (#0065d1), accent-2 (#7b00ff)

2. **custom-functions** overrides this via `wp_theme_json_data_theme` filter with CVRS brand colors: Action Base (#00508d), Accent (#2987cf), Deep Navy (#03003c), Orange Accent (#DB775F), Footer Blue (#001A2D), and others

The plugin-injected palette is what renders on the frontend. Use CSS custom properties (e.g., `var(--wp--preset--color--action-base)`) when styling.

## Layout Dimensions

- Content width: **768px**
- Wide width: **1200px**

## Key Hooks & Filters to Know

| Hook | Location | What It Does |
|------|----------|-------------|
| `wp_theme_json_data_theme` | custom-functions | Injects CVRS color palette + Navigation block styles |
| `pre_get_posts` | insights-filter block | Filters main blog query by role taxonomy and search param |
| `acf/init` | acf-core.php | Registers all active ACF blocks |
| `acf/load_field/name=menu_select` | menu block | Dynamically populates menu dropdown from registered WP menus |
| `post_thumbnail_html` | custom-functions | Provides fallback featured image |
| `wp_footer` | video-button, custom-admin-bar | Injects modal layers and admin toolbar |

## Settings & Configuration

- **GTM Container ID:** `wp_options` key `custom_functions_gtm_id` — configured in Settings → Tracking Scripts
- **Google Ads Conversion ID:** `wp_options` key `custom_functions_gads_id` — same settings page
- **Design Variables:** Appearance → Design Variables — read-only reference of active theme.json values

## File Paths That Matter

```
wp-content/
├── plugins/
│   ├── acf-core-master/          # Block system — most development happens here
│   │   ├── acf-core.php          # Auto-loader entry point
│   │   ├── active/               # Live blocks (each is a self-contained module)
│   │   └── inactive/             # Deactivated blocks (preserved for reuse)
│   ├── custom-functions/         # CPTs, taxonomies, tracking, color palette
│   │   └── index.php             # Single-file plugin, all logic here
│   ├── custom-styles/            # Global CSS
│   │   └── custom-styles.css     # THE main stylesheet — edit this for global styles
│   ├── custom-scripts/           # Global JS
│   │   └── script.js             # THE main script file
│   ├── design-variables/         # Admin reference tool (read-only, rarely edited)
│   └── custom-front-admin-bar-master/  # Admin toolbar
└── themes/
    └── ultra-empty-fse/
        └── theme.json            # Layout + base palette + typography (NOT the live palette)
```
