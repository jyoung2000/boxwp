# Changelog

All notable changes to the Box AI Search WordPress plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-XX

### Fixed
- **Critical**: Fixed autoloader to properly load classes from `admin/` and `public/` directories
  - Autoloader now correctly maps `BoxAISearch\Admin\*` to `admin/`
  - Autoloader now correctly maps `BoxAISearch\PublicInterface\*` to `public/`
  - Resolves fatal error on plugin activation
  - Updated composer.json PSR-4 mapping for consistency
- **Cache**: Fixed "Clear Cache" button error
  - Changed Cache::clear_all() to return array with count or WP_Error
  - Improved error handling for database failures
  - Added user-friendly message when cache is already empty
  - Shows count of cleared items on success (singular/plural)
  - Properly handles edge cases (0 items, database errors)

### Added

#### Setup Wizard
- **New**: Interactive setup wizard for easy frontend configuration
  - Automatic encryption key generation
  - Auto-configuration attempts to write to wp-config.php if permissions allow
  - Manual configuration with copy-to-clipboard functionality
  - Step-by-step guided setup process
  - Admin notice for incomplete setup
  - Visual progress tracking through setup steps
  - Link to wizard from settings page
- **Setup Wizard Features**:
  - Check for OpenSSL availability
  - Generate secure AES-256 encryption keys
  - Automatic wp-config.php modification (when file is writable)
  - One-click copy to clipboard for manual setup
  - Direct links to Box Developer Console
  - Integrated Box credentials configuration
  - Setup completion confirmation
- **User Experience**:
  - Dismissible setup notice
  - Skip wizard option
  - Accessible from Settings > Box AI Search
  - Mobile-responsive wizard interface
  - Clear error messaging and troubleshooting

#### JSON Configuration Upload
- **New**: One-click Box JSON configuration file upload
  - Upload Box Developer Console JSON file directly in settings
  - Automatically extracts and encrypts all credentials
  - Supports both JWT and Client Credentials authentication
  - Real-time import status with detailed feedback
  - Auto-populates Client ID, Client Secret, Enterprise ID, and JWT credentials
  - File validation (JSON format, max 1MB)
  - Secure file handling with immediate deletion after processing
  - Page auto-refresh after successful import
  - Highlighted upload section at top of credentials form
- **AJAX Improvements**:
  - Fixed document.ready wrapping for proper event binding
  - Changed to delegated event handlers for dynamic content
  - Improved error handling and user feedback
  - Added FormData support for file uploads

#### Core Features
- Box AI semantic document search integration via `/2.0/ai/ask` endpoint
- `[box_ai_search]` shortcode for easy deployment
- Real-time AJAX search with auto-debouncing (300ms)
- Responsive search interface with mobile support

#### Security
- AES-256-CTR encryption for all sensitive credentials
- OpenSSL-based encryption with keys stored in wp-config.php
- WordPress security best practices implemented:
  - Nonce verification for all AJAX requests
  - Input sanitization with `sanitize_text_field()`
  - Output escaping with context-appropriate functions
  - Capability checks with `current_user_can()`
- Secure credential storage in encrypted format
- WP_Error implementation for safe error handling

#### Performance
- WordPress Transients caching layer (1-hour default)
- Configurable cache expiration (60-86400 seconds)
- Object cache support for Redis/Memcached
- Conditional asset loading (only on pages with shortcode)
- Search query debouncing to prevent excessive API calls
- Target <500ms response time for cached queries

#### Authentication
- JWT authentication support for Box API
- Client Credentials Grant support
- Automatic token refresh mechanism
- Token caching (55-minute expiration)
- Enterprise authentication support

#### Admin Interface
- Complete settings page at Settings > Box AI Search
- Secure credential configuration form
- Connection testing tool
- Cache management tools
- Cache statistics display
- Setup wizard with encryption key generation
- Settings link on plugins page

#### Developer Features
- PSR-4 autoloading with `BoxAISearch\` namespace
- Object-oriented PHP architecture
- WordPress Coding Standards compliance
- Comprehensive PHPDoc blocks
- Plugin activation/deactivation hooks
- Uninstall cleanup handler
- Filter and action hooks for extensibility

#### Testing
- PHPUnit test suite
- Encryption class tests
- Test bootstrap configuration
- phpunit.xml configuration

#### Documentation
- Comprehensive README.md
- Detailed INSTALL.md guide
- SECURITY.md policy
- Inline code documentation
- Setup instructions
- Troubleshooting guide
- API reference

#### Internationalization
- Translation-ready with text domain 'box-ai-search'
- Complete localization support
- POT file generation support

### Code Structure

```
box-ai-search/
├── admin/              # Admin interface
├── includes/           # Core classes
├── public/             # Frontend assets
├── tests/              # PHPUnit tests
└── languages/          # Translation files
```

### Technical Specifications

- **WordPress**: 6.4+ required
- **PHP**: 8.0+ required
- **PHP Extensions**: OpenSSL required
- **Namespace**: BoxAISearch\
- **Prefix**: bas_
- **Text Domain**: box-ai-search

### Security

- All sensitive data encrypted before storage
- Encryption keys never stored in database
- Nonce verification on all AJAX endpoints
- Capability checks on all admin functions
- SQL injection prevention with prepared statements
- XSS prevention with output escaping
- CSRF protection with nonce verification

### Performance Metrics

- Cached queries: <500ms
- Uncached queries: <2000ms (Box AI dependent)
- Search debounce: 300ms
- Token cache: 55 minutes
- Default result cache: 1 hour

### Browser Support

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

### Known Limitations

- JWT generation requires external library (not included)
- WordPress test suite required for full test execution
- Box AI features must be enabled on Box account
- Requires Box Enterprise or Business account
- Search requires minimum 3 characters

### Dependencies

#### Required
- WordPress 6.4+
- PHP 8.0+
- OpenSSL PHP extension

#### Recommended
- Object cache (Redis/Memcached)
- HTTPS/SSL enabled
- WordPress debug logging

#### Development
- PHPUnit 9.5+
- Composer
- WP Coding Standards

### Migration Notes

This is the initial release. No migration required.

### Upgrade Notice

#### 1.0.0
Initial release. Requires PHP 8.0+ and WordPress 6.4+. After installation, configure encryption key in wp-config.php before use.

---

## Release Types

- **Major** (X.0.0): Breaking changes, major features
- **Minor** (1.X.0): New features, enhancements
- **Patch** (1.0.X): Bug fixes, security patches

## Versioning

We use [Semantic Versioning](https://semver.org/):
- MAJOR: Incompatible API changes
- MINOR: Backwards-compatible functionality
- PATCH: Backwards-compatible bug fixes

## Support

- Security issues: See SECURITY.md
- Bug reports: GitHub Issues
- Feature requests: GitHub Discussions
- Documentation: README.md, INSTALL.md

---

[1.0.0]: https://github.com/jyoung2000/boxwp/releases/tag/v1.0.0
