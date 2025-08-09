# Energy Alabama Knowledge Center Plugin

A comprehensive knowledge center system for Energy Alabama with custom post types, taxonomies, and search functionality.

## Development Setup

### Prerequisites
- PHP 7.4 or higher
- Composer
- WordPress development environment

### Installation

1. Clone the repository:
```bash
git clone git@github.com:erik-itb/ea-kc.git
cd ea-kc
```

2. Install dependencies:
```bash
composer install
```

3. The setup includes WordPress stubs for IDE support, which resolves VSCode errors for WordPress core functions like `plugin_dir_url()`, `wp_enqueue_script()`, etc.

### IDE Configuration

The project includes VSCode configuration (`.vscode/settings.json`) that:
- Enables WordPress function recognition
- Configures Intelephense with WordPress stubs
- Sets up proper PHP validation

### Code Analysis

Run PHPStan for static analysis:
```bash
./vendor/bin/phpstan analyse
```

### File Structure

```
energy-alabama-kc/
├── assets/                 # CSS, JS, and image assets
├── includes/              # PHP classes and core functionality
│   ├── admin/            # Admin-specific classes
│   ├── core/             # Core plugin functionality
│   ├── elementor/        # Elementor integration
│   ├── frontend/         # Frontend functionality
│   └── utils/            # Utility classes
├── languages/            # Translation files
├── templates/            # Template files
├── tests/               # Test files
├── vendor/              # Composer dependencies (not in git)
├── composer.json        # Composer configuration
├── phpstan.neon         # PHPStan configuration
└── energy-alabama-kc.php # Main plugin file
```

## Features

- Custom post types for knowledge center articles and dockets
- Custom taxonomies for organization
- Advanced search functionality
- Elementor widget integration
- Multi-language support
- Bulk import capabilities

## License

GPL v2 or later
