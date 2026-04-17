# WordPress Site Testing Suite

A reusable testing package for WordPress client sites with security, performance, and version checking.

## Structure

```
tests/
├── config/                     # Configuration files
│   ├── lighthouse-pages.yml   # URLs and thresholds for Lighthouse
│   ├── plugins.yml            # Plugin/theme version tracking
│   └── test-config.yml        # Master configuration
├── scripts/                    # Test scripts
│   ├── check-versions.py      # Plugin vulnerability scanner
│   └── lighthouse-audit.py    # Performance testing
├── phpcs/                      # Code standards
│   └── phpcs.xml              # WordPress security ruleset
└── README.md                   # This file
```

## Making This Package Reusable

### 1. For New Client Sites

Copy the entire `tests/` directory to your new project:

```bash
cp -r tests/ /path/to/new-site/tests/
```

### 2. Customize Configuration

Edit `tests/config/test-config.yml`:
- Update `site.name` and `site.url`
- Adjust test thresholds as needed
- List your custom plugins/themes

### 3. Update Plugin List

Edit `tests/config/plugins.yml`:
- List all third-party plugins with their versions
- This enables vulnerability scanning

### 4. Configure Pages to Test

Edit `tests/config/lighthouse-pages.yml`:
- Add URLs to test
- Set performance thresholds per page

### 5. GitHub Actions Setup

Copy `.github/workflows/security-scan.yml` and ensure these secrets are set:
- `WORDFENCE_API_TOKEN` - For vulnerability checks
- `PAGESPEED_API_KEY` - For Lighthouse audits

## Running Tests Locally

### PHPCS Security Scan
```bash
phpcs --standard=tests/phpcs/phpcs.xml
```

### Version Check
```bash
MANIFEST_PATH=tests/config/plugins.yml python tests/scripts/check-versions.py
```

### Lighthouse Audit
```bash
LIGHTHOUSE_CONFIG=tests/config/lighthouse-pages.yml \
PAGESPEED_API_KEY=your_key \
python tests/scripts/lighthouse-audit.py
```

## Customization Options

### Adding Custom Plugins to Test

In `tests/phpcs/phpcs.xml`, add:
```xml
<file>../../wp-content/plugins/your-plugin</file>
```

### Adjusting Security Rules

Modify `tests/phpcs/phpcs.xml` to exclude specific rules:
```xml
<exclude name="WordPress.Security.SpecificRule"/>
```

### Performance Thresholds

In `tests/config/lighthouse-pages.yml`:
```yaml
thresholds:
  performance: 80      # Adjust as needed
  accessibility: 85
  best-practices: 80
  seo: 90
```

## Package as Composer Dependency (Future)

To make this truly reusable across projects:

1. Extract to separate repository
2. Add `composer.json`:
```json
{
  "name": "your-company/wp-test-suite",
  "type": "library",
  "scripts": {
    "test:security": "phpcs --standard=phpcs/phpcs.xml",
    "test:versions": "python scripts/check-versions.py",
    "test:lighthouse": "python scripts/lighthouse-audit.py"
  }
}
```

3. Install in projects:
```bash
composer require --dev your-company/wp-test-suite
```

## Support

For issues or improvements, contact the development team.