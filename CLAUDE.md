# Energy Alabama Knowledge Center Plugin - Development Context

## Project Overview
WordPress plugin for Energy Alabama's Knowledge Center featuring bilingual content management (English/Spanish), custom post types, AJAX search, and comprehensive article filtering.

## Current Status: COMPLETED FEATURES

### ✅ Dashboard UI Fixes
- Fixed admin dashboard box header margins
- Corrected "View Knowledge Center" button URL from `/kc-articles` to `/knowledge-center`
- Removed unnecessary "Plugin Information" section

### ✅ Template Standardization
- Standardized hero sections across all templates to show only "Energy Alabama Knowledge Center" header and search bar
- Removed post excerpts from individual article hero sections
- Created missing archive templates: `archive-kc-article.php`, `taxonomy-kc-tag.php`, `taxonomy-docket-jurisdiction.php`

### ✅ Typography & CSS Fixes
- Fixed excessive h1 margins (was 80px top, 40px bottom)
- Implemented comprehensive typography system with reasonable margins
- Fixed search dropdown z-index issues by changing `.eakc-hero` from `overflow: hidden` to `overflow: visible`

### ✅ AJAX Search Implementation
- Created `/assets/js/search.js` with live search functionality
- Added debouncing, keyboard navigation, and dropdown results
- Implemented thumbnail fallbacks using placehold.it
- Added search endpoint in `/includes/class-frontend.php`

### ✅ Spanish Content Management
- Enhanced Spanish Content metabox with mutual exclusion logic
- Added bidirectional language linking in single article templates
- Created `eakc_find_english_version()` function for cross-language navigation

### ✅ Recent Articles Standardization
- Updated Recent Articles section on main knowledge center page to show 3 articles
- Consistent card styling matching category templates

### ✅ Language Filtering System
- Added language filter dropdown to all article listing templates
- Implemented `eakc_filterByLanguage()` JavaScript function
- Added proper CSS styling for language filter controls
- Default behavior: English Only content shown on page load

### ✅ Icon Picker Implementation with Phosphor Icons **[JUST COMPLETED]**
- Switched from Font Awesome to Phosphor Icons (cleaner, modern design)
- Created searchable modal interface for icon selection
- Added WordPress color picker for custom icon colors
- Category filtering (Regular and Fill styles)
- Implemented real-time search with debouncing
- Icon data stored in `/assets/js/icon-picker-data.js`
- Icons display with custom colors on article cards
- Each article can have unique icon and color combination

## Key Files Modified

### Templates
- `/templates/single-kc-article.php` - Bidirectional Spanish linking
- `/templates/single-docket.php` - Hero standardization
- `/templates/archive-kc-article.php` - Created with language filtering
- `/templates/taxonomy-kc-category.php` - Language filtering added
- `/templates/taxonomy-kc-tag.php` - Created with language filtering
- `/templates/taxonomy-docket-jurisdiction.php` - Created
- `/templates/page-knowledge-center.php` - Recent Articles updated

### Admin
- `/includes/admin/class-admin.php` - Dashboard fixes
- `/includes/admin/class-meta-boxes.php` - Spanish content mutual exclusion

### Assets
- `/assets/css/frontend.css` - Typography, z-index, language filter styling
- `/assets/css/meta-boxes.css` - Icon picker modal styles
- `/assets/js/search.js` - AJAX search functionality
- `/assets/js/meta-boxes.js` - Icon picker and color picker functionality
- `/assets/js/icon-picker-data.js` - Phosphor Icons definitions

### Backend
- `/includes/class-frontend.php` - AJAX search endpoint

## Technical Implementation Details

### Language System
- Uses `_eakc_is_spanish_content` meta field to identify Spanish articles
- Language filter dropdown options: "All Languages", "English Only" (default), "Spanish Only"
- JavaScript filtering based on `data-language` attribute on article cards
- Automatic filtering to English-only content on page load

### Icon System
- Uses Phosphor Icons library (v2.0.3) via CDN
- Two icon styles: Regular and Fill
- Custom color picker using WordPress built-in color picker
- Stores icon class in `_eakc_featured_icon` meta field
- Stores icon color in `_eakc_icon_color` meta field (default: #ffffff)
- Icons display on blue gradient background when no featured image

### Search Functionality
- Live AJAX search with 300ms debounce
- Keyboard navigation (arrow keys, enter, escape)
- Thumbnail generation with fallbacks
- Security: WordPress nonce verification

### Template Architecture
- Consistent hero sections across all templates
- Unified card styling for article listings
- Responsive filter controls with proper styling
- Pagination support on all archive pages

## Development Patterns Established

### CSS Structure
- BEM-like naming convention with `eakc-` prefix
- Responsive design with mobile-first approach
- Consistent spacing and typography scale

### JavaScript Patterns
- Vanilla JavaScript (no jQuery dependency)
- Event delegation and proper event handling
- Consistent function naming: `eakc_functionName()`

### PHP Patterns
- Proper WordPress hooks and filters
- Security: nonce verification, input sanitization
- Internationalization ready with `_e()` and `__()` functions

## Known Working Features
1. ✅ Dashboard admin interface
2. ✅ Article creation and management
3. ✅ Category and tag taxonomies
4. ✅ AJAX live search
5. ✅ Bilingual content management
6. ✅ Language filtering on all listing pages
7. ✅ Responsive design
8. ✅ Template hierarchy

## Next Potential Improvements
- Additional filter options (by date range, read time)
- Enhanced mobile navigation
- Performance optimization for large article counts
- SEO enhancements for bilingual content
- Extended icon library with custom icon upload option

## Development Commands
- No specific build process required
- Standard WordPress development environment
- CSS and JS files loaded directly (no compilation needed)

---
*Last updated: Icon picker switched to Phosphor Icons with color customization*
*Status: All current requirements fulfilled, including enhanced icon picker with color selection*