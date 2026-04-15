# Agent Conventions

Rules for AI agents working on this codebase. Read [CLAUDE.md](CLAUDE.md) first for architecture context.

## Golden Rules

1. **Never edit WordPress core** (`wp-admin/`, `wp-includes/`, root PHP files). Changes won't persist.
2. **Never edit the theme for functionality.** `ultra-empty-fse` is a layout shell. Add features through plugins.
3. **Never edit third-party plugins.** If you need to modify behavior, use hooks/filters in `custom-functions` or a new plugin.
4. **All CLI commands must use `studio wp`**, not bare `wp`. See [STUDIO.md](STUDIO.md).

## Creating a New ACF Block

1. Create a folder: `wp-content/plugins/acf-core-master/active/{your-block-name}/`
2. Add `module.php` — register with `acf_register_block()`, set category to `acf-core-blocks`
3. Add `acf.json` — define field group, load it with `acf_add_local_field_group()`
4. Add `template.php` — render output using `get_field()` calls
5. Add `css/` and optionally `js/` directories for block assets
6. The auto-loader in `acf-core.php` will detect it — no registration elsewhere needed

Copy an existing block (e.g., `faq` or `copy-permalink`) as a starter template.

## Creating a New Plugin

For functionality that doesn't belong in an existing plugin:

1. Create folder: `wp-content/plugins/{plugin-name}/`
2. Add main PHP file with standard plugin header comment
3. Use hooks and filters — never modify other plugins or core directly
4. Add the plugin folder to the "Custom Plugins" section of `CLAUDE.md`

## Styling Conventions

- Global styles go in `custom-styles/custom-styles.css`
- Block-specific styles go in the block's own `css/` directory
- Use WordPress CSS custom properties for colors: `var(--wp--preset--color--{slug})`
- The CVRS palette (injected by custom-functions) is the live palette, not theme.json's base palette
- No CSS framework — vanilla CSS only

## JavaScript Conventions

- Global scripts go in `custom-scripts/script.js`
- Block-specific scripts go in the block's own `js/` directory
- Vanilla JS preferred. jQuery is available where ACF blocks need it.
- No build step — files are served directly

## Database Notes

- Studio uses **SQLite**, not MySQL. Standard `$wpdb` queries work but avoid MySQL-specific syntax.
- No `FULLTEXT` indexes. Use `LIKE` queries for search.
- Do not reference `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` — they are not defined.

## Testing Changes

```bash
# Check if a function exists
studio wp eval 'echo function_exists("my_function") ? "yes" : "no";'

# Verify a plugin is active
studio wp plugin list --status=active --format=csv

# Check for PHP errors
studio site set --debug-log --debug-display
cat wp-content/debug.log

# Flush caches after changes
studio wp cache flush
```

## What to Update When You Change Things

- **New custom plugin?** → Add to CLAUDE.md's custom plugins table
- **New ACF block?** → Add to CLAUDE.md's active blocks table
- **New CPT or taxonomy?** → Add to CLAUDE.md's CPT table
- **New hook that other code depends on?** → Add to CLAUDE.md's hooks table
- **Deactivated a block?** → Move from active to inactive in CLAUDE.md's block table
