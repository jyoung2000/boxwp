# Box AI Search - WordPress Plugin

A secure WordPress plugin that integrates Box AI's semantic document search, enabling users to search Box documents via a shortcode-based interface with real-time AJAX results.

## Features

- **🧙 Interactive Setup Wizard**: Guided frontend configuration with automatic encryption key generation
- **🔍 Semantic Search**: Leverage Box AI's powerful semantic search capabilities
- **⚡ Easy Integration**: Simple `[box_ai_search]` shortcode for quick deployment
- **🚀 Real-time Results**: AJAX-powered search with 300ms debouncing for optimal performance
- **🔐 Military-Grade Encryption**: AES-256-CTR encryption for all API credentials
- **💾 Smart Caching**: Aggressive caching with WordPress Transients (1-hour default, configurable)
- **📱 Responsive Design**: Mobile-friendly search interface
- **🛡️ Security First**: Implements WordPress security best practices (nonces, sanitization, escaping)
- **⚙️ Performance Optimized**: Assets loaded only on pages with shortcode, <500ms for cached queries

## Requirements

- **WordPress**: 6.4 or higher
- **PHP**: 8.0 or higher
- **PHP Extensions**: OpenSSL (for encryption)
- **Box Account**: Box application with API credentials

## Installation

### Option 1: Install from ZIP (Recommended)

1. **Build the ZIP file**:
   ```bash
   cd box-ai-search
   ./quick-build.sh
   ```
   This creates `dist/box-ai-search-1.0.0.zip`

2. **Upload to WordPress**:
   - Go to **Plugins > Add New**
   - Click **Upload Plugin**
   - Choose the ZIP file
   - Click **Install Now**
   - Click **Activate Plugin**

See [BUILD.md](BUILD.md) for detailed build instructions.

### Option 2: Manual Installation

1. Upload the `box-ai-search` directory to `/wp-content/plugins/`
2. Activate "Box AI Search" through the 'Plugins' menu in WordPress

### After Installation

**Recommended: Use the Setup Wizard!**

After activating the plugin, you'll see a setup notice. Click **"Start Setup Wizard"** for a guided configuration experience, or navigate to the wizard manually:

- **Admin > Box AI Search Setup** (from the notice)
- **Settings > Box AI Search** then click "Launch Setup Wizard"

The wizard will:
1. ✅ Generate encryption key automatically
2. ✅ Attempt to configure wp-config.php (if writable)
3. ✅ Provide copy-paste instructions (if manual setup needed)
4. ✅ Guide you through Box credentials setup
5. ✅ Verify your configuration

#### Manual Setup (Alternative)

If you prefer manual configuration:

**1. Configure Encryption Key**

Add the following constant to your `wp-config.php` file (before "That's all, stop editing!" line):

```php
define( 'BAS_ENCRYPTION_KEY', 'YOUR_GENERATED_KEY_HERE' );
```

To generate a secure key, visit the plugin settings page at **Settings > Box AI Search**. The page displays a unique encryption key that you should copy to your `wp-config.php`.

Alternatively, generate a key programmatically:

```php
echo base64_encode( openssl_random_pseudo_bytes( 32 ) );
```

**2. Configure Box API Credentials**

1. Navigate to **Settings > Box AI Search**
2. Enter your Box API credentials:
   - **Client ID**: Your Box application Client ID
   - **Client Secret**: Your Box application Client Secret
   - **Enterprise ID**: (Optional, for JWT authentication)
   - **Public Key ID**: (Optional, for JWT authentication)
   - **Private Key**: (Optional, for JWT authentication)
   - **Private Key Passphrase**: (Optional, if applicable)

3. Click **Save Settings**
4. Click **Test Box API Connection** to verify credentials

**3. Add Shortcode**

Add the `[box_ai_search]` shortcode to any page or post where you want the search interface to appear.

## Box API Setup

### Creating a Box Application

1. Go to [Box Developer Console](https://app.box.com/developers/console)
2. Click **Create New App**
3. Choose **Custom App**
4. Select authentication method:
   - **OAuth 2.0 with JWT** (recommended for server-to-server)
   - **Client Credentials Grant** (alternative)
5. Name your application
6. Copy your credentials to the WordPress plugin settings

### Required Box Permissions

Your Box application needs the following scopes:
- Read all files and folders
- Write all files and folders (if needed)
- Manage AI operations

### Box AI Features

Ensure that Box AI features are enabled for your enterprise account. Contact Box support if Box AI is not available.

## Usage

### Basic Shortcode

```
[box_ai_search]
```

### Shortcode Attributes

Customize the search interface with these attributes:

```
[box_ai_search placeholder="Search documents..." button_text="Search"]
```

**Available Attributes:**
- `placeholder` - Custom placeholder text for search input (default: "Search Box documents...")
- `button_text` - Custom text for search button (default: "Search")

### Examples

**Custom placeholder:**
```
[box_ai_search placeholder="Find your files..."]
```

**Custom button text:**
```
[box_ai_search button_text="Go"]
```

**Both custom:**
```
[box_ai_search placeholder="What are you looking for?" button_text="Find It"]
```

## Configuration

### Cache Settings

Configure cache behavior in **Settings > Box AI Search**:

- **Cache Expiration**: Set cache duration in seconds (60-86400)
  - Default: 3600 (1 hour)
  - Recommended: 3600-7200 for most use cases

### Clearing Cache

Use the **Clear All Cached Results** button in plugin settings to:
- Clear all cached search results
- Force fresh data from Box AI
- Useful after making changes to Box content

## Security

### Encryption

All sensitive credentials are encrypted using AES-256-CTR before storage:
- Client ID and Secret
- Private keys and passphrases
- Enterprise IDs

Encryption keys are stored in `wp-config.php` (never in the database).

### Best Practices

1. **Protect wp-config.php**: Ensure file permissions are set to 400 or 440
2. **Use HTTPS**: Always use SSL/TLS for your WordPress site
3. **Regular Updates**: Keep WordPress and the plugin updated
4. **Limit Access**: Only give admin access to trusted users
5. **Backup Credentials**: Store Box credentials securely offline

### WordPress Security Standards

The plugin implements:
- Nonce verification for all AJAX requests
- Input sanitization with `sanitize_text_field()`
- Output escaping with `esc_html()`, `esc_attr()`, `esc_url()`
- Capability checks with `current_user_can()`
- Prepared SQL statements (where applicable)

## Performance

### Optimization Features

- **Conditional Asset Loading**: CSS/JS only loaded on pages with shortcode
- **Aggressive Caching**: Search results cached for quick retrieval
- **Debouncing**: 300ms search debouncing prevents excessive API calls
- **Object Cache Support**: Automatically uses object cache if available
- **Transient API**: Leverages WordPress Transients for flexible caching

### Performance Targets

- Cached queries: <500ms
- Uncached queries: <2000ms (depending on Box AI response)
- Search debounce: 300ms

### Monitoring

View cache statistics in **Settings > Box AI Search**:
- Number of cached items
- Object cache status
- Cache expiration settings

## Development

### File Structure

```
box-ai-search/
├── admin/
│   ├── js/
│   │   └── admin.js          # Admin JavaScript
│   └── Settings.php          # Admin settings page
├── includes/
│   ├── Activator.php         # Plugin activation
│   ├── BoxAI.php             # Box AI API client
│   ├── Cache.php             # Caching layer
│   ├── Deactivator.php       # Plugin deactivation
│   ├── Encryption.php        # Encryption class
│   └── Plugin.php            # Core plugin class
├── public/
│   ├── css/
│   │   └── frontend.css      # Frontend styles
│   ├── js/
│   │   └── frontend.js       # Frontend JavaScript
│   └── Shortcode.php         # Shortcode handler
├── tests/
│   ├── bootstrap.php         # Test bootstrap
│   └── EncryptionTest.php    # Encryption tests
├── box-ai-search.php         # Main plugin file
├── phpunit.xml               # PHPUnit configuration
└── README.md                 # This file
```

### Coding Standards

The plugin follows [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/):
- PSR-4 autoloading with `BoxAISearch\` namespace
- Tabs for indentation
- Yoda conditions
- Snake_case for function names
- Complete PHPDoc blocks
- Prefix: `bas_` for all functions/options

### Running Tests

```bash
# Install PHPUnit
composer require --dev phpunit/phpunit

# Run tests
vendor/bin/phpunit
```

### Internationalization

The plugin is translation-ready with text domain `box-ai-search`. Translation files should be placed in `/languages/`.

To generate a POT file:

```bash
wp i18n make-pot . languages/box-ai-search.pot
```

## Troubleshooting

### Connection Test Fails

**Problem**: "Connection test failed" error

**Solutions**:
1. Verify Box API credentials are correct
2. Ensure encryption key is defined in `wp-config.php`
3. Check that your Box app has required permissions
4. Verify your WordPress server can reach `api.box.com`
5. Check PHP error logs for detailed error messages

### Encryption Not Available

**Problem**: "Encryption is not available" warning

**Solutions**:
1. Ensure OpenSSL PHP extension is installed
2. Add encryption key to `wp-config.php`
3. Verify key is properly formatted (base64 encoded, 32 bytes decoded)

### No Search Results

**Problem**: Search returns no results

**Solutions**:
1. Verify Box account has accessible documents
2. Check that Box AI features are enabled
3. Clear plugin cache and try again
4. Ensure search query is at least 3 characters
5. Test Box API connection in settings

### Performance Issues

**Problem**: Slow search response times

**Solutions**:
1. Enable object caching (Redis, Memcached)
2. Increase cache expiration time
3. Check WordPress server resources
4. Verify network connectivity to Box servers
5. Monitor Box API rate limits

### JavaScript Errors

**Problem**: Search interface not working

**Solutions**:
1. Check browser console for JavaScript errors
2. Verify jQuery is loaded
3. Check for JavaScript conflicts with other plugins
4. Clear browser cache
5. Disable other plugins to test for conflicts

## API Reference

### Shortcode: `[box_ai_search]`

Renders the search interface.

**Parameters:**
- `placeholder` (string): Search input placeholder text
- `button_text` (string): Search button text

**Returns:** HTML search interface

### Filter Hooks

```php
// Modify cache expiration
add_filter( 'bas_cache_expiration', function( $expiration ) {
    return 7200; // 2 hours
}, 10, 1 );

// Modify search results
add_filter( 'bas_search_results', function( $results, $query ) {
    // Modify results
    return $results;
}, 10, 2 );
```

### Action Hooks

```php
// Before search
add_action( 'bas_before_search', function( $query ) {
    // Do something before search
}, 10, 1 );

// After search
add_action( 'bas_after_search', function( $query, $results ) {
    // Do something after search
}, 10, 2 );
```

## Changelog

### 1.0.0 - 2025-01-XX

**Added:**
- Initial release
- Box AI semantic search integration
- AES-256-CTR encryption for credentials
- WordPress Transients caching
- AJAX search with debouncing
- Admin settings interface
- PHPUnit tests
- Comprehensive documentation

## Support

For issues and questions:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review the [Box AI documentation](https://developer.box.com/guides/box-ai/)
3. Open an issue on [GitHub](https://github.com/jyoung2000/boxwp/issues)

## License

GPL v2 or later

## Credits

- Developed for WordPress 6.4+
- Powered by Box AI
- Built with security and performance in mind

## Privacy

This plugin:
- Stores encrypted API credentials in WordPress database
- Sends search queries to Box AI API
- Caches search results locally
- Does not collect user data
- Does not send data to third parties (except Box API)

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Follow WordPress Coding Standards
4. Add tests for new features
5. Submit a pull request

---

Made with ❤️ for the WordPress and Box communities
