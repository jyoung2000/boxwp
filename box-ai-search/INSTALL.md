# Box AI Search - Installation Guide

Complete step-by-step installation and configuration guide.

## Prerequisites

Before installing the Box AI Search plugin, ensure you have:

### WordPress Environment
- ✅ WordPress 6.4 or higher
- ✅ PHP 8.0 or higher
- ✅ OpenSSL PHP extension
- ✅ HTTPS enabled (recommended)
- ✅ Write permissions on wp-config.php

### Box Account
- ✅ Box Enterprise or Business account
- ✅ Box AI features enabled
- ✅ Admin access to Box Developer Console

## Installation Steps

### Step 1: Upload Plugin Files

**Option A: Via WordPress Admin**

1. Download the plugin ZIP file
2. Go to **Plugins > Add New**
3. Click **Upload Plugin**
4. Choose the ZIP file
5. Click **Install Now**

**Option B: Via FTP/SFTP**

1. Extract the plugin ZIP file
2. Upload the `box-ai-search` folder to `/wp-content/plugins/`
3. Ensure proper file permissions (644 for files, 755 for directories)

**Option C: Via WP-CLI**

```bash
wp plugin install /path/to/box-ai-search.zip
```

### Step 2: Activate Plugin

**Via WordPress Admin:**
1. Go to **Plugins**
2. Find "Box AI Search"
3. Click **Activate**

**Via WP-CLI:**
```bash
wp plugin activate box-ai-search
```

### Step 3: Configure Encryption

#### Generate Encryption Key

**Method 1: Via Plugin Settings**

1. Go to **Settings > Box AI Search**
2. Scroll to "Setup Instructions"
3. Copy the generated encryption key

**Method 2: Via PHP**

Run this PHP code to generate a key:

```php
<?php
echo base64_encode( openssl_random_pseudo_bytes( 32 ) );
```

**Method 3: Via WP-CLI**

```bash
wp eval "echo base64_encode( openssl_random_pseudo_bytes( 32 ) );"
```

**Method 4: Via Command Line**

```bash
openssl rand -base64 32
```

#### Add Key to wp-config.php

1. Open `wp-config.php` in a text editor
2. Find the line: `/* That's all, stop editing! Happy publishing. */`
3. **Above** that line, add:

```php
define( 'BAS_ENCRYPTION_KEY', 'YOUR_GENERATED_KEY_HERE' );
```

Example:

```php
// Box AI Search encryption key
define( 'BAS_ENCRYPTION_KEY', 'VGhpc0lzQW5FeGFtcGxlS2V5Rm9yVGVzdGluZw==' );

/* That's all, stop editing! Happy publishing. */
```

4. Save the file
5. **Important**: Set file permissions to 400 or 440

```bash
chmod 400 wp-config.php
```

### Step 4: Set Up Box Application

#### Create Box Application

1. Go to [Box Developer Console](https://app.box.com/developers/console)
2. Click **Create New App**
3. Select **Custom App**
4. Choose authentication method:
   - **OAuth 2.0 with JWT** (Recommended)
   - **Client Credentials Grant** (Alternative)

#### Configure OAuth 2.0 with JWT

1. **App Basics**
   - Name: "WordPress Box AI Search"
   - Description: "AI-powered document search for WordPress"

2. **Configuration**
   - Application Scopes:
     - ✅ Read all files and folders
     - ✅ Write all files and folders (optional)
     - ✅ Manage enterprise properties

3. **Advanced Features**
   - ✅ Enable "Make API calls using the as-user header"
   - ✅ Enable "Generate user access tokens"

4. **Add and Manage Public Keys**
   - Click **Generate a Public/Private Keypair**
   - Download the JSON config file
   - **Keep this file secure!**

5. **Enterprise ID**
   - Copy your Enterprise ID
   - Found in: Account Settings > Business Settings > Account Info

#### Configure Client Credentials Grant

If using Client Credentials instead of JWT:

1. **App Basics**
   - Same as above

2. **Configuration**
   - Application Scopes:
     - ✅ Manage Box AI
     - ✅ Read all files and folders

3. **Get Credentials**
   - Copy Client ID
   - Copy Client Secret
   - Copy Enterprise ID

### Step 5: Configure Plugin Settings

1. Go to **Settings > Box AI Search**
2. Enter your Box API credentials:

#### For JWT Authentication:

Open the downloaded JSON config file and enter:

- **Client ID**: Value of `boxAppSettings.clientID`
- **Client Secret**: Value of `boxAppSettings.clientSecret`
- **Enterprise ID**: Value of `enterpriseID`
- **Public Key ID**: Value of `boxAppSettings.appAuth.publicKeyID`
- **Private Key**: Value of `boxAppSettings.appAuth.privateKey` (entire key including headers)
- **Private Key Passphrase**: Value of `boxAppSettings.appAuth.passphrase`

Example JSON structure:
```json
{
  "boxAppSettings": {
    "clientID": "abc123...",
    "clientSecret": "xyz789...",
    "appAuth": {
      "publicKeyID": "key123",
      "privateKey": "-----BEGIN ENCRYPTED PRIVATE KEY-----\n...\n-----END ENCRYPTED PRIVATE KEY-----\n",
      "passphrase": "passphrase123"
    }
  },
  "enterpriseID": "12345"
}
```

#### For Client Credentials:

- **Client ID**: Your Box app Client ID
- **Client Secret**: Your Box app Client Secret
- **Enterprise ID**: Your Box Enterprise ID

3. Click **Save Settings**

### Step 6: Test Connection

1. Scroll to **Tools** section
2. Click **Test Box API Connection**
3. Wait for response

**Success**: "Connection successful! Your Box API credentials are working correctly."

**Failure**: See [Troubleshooting](#troubleshooting) section

### Step 7: Configure Cache (Optional)

1. In plugin settings, find **Cache Settings**
2. Set **Cache Expiration** in seconds:
   - Minimum: 60 (1 minute)
   - Default: 3600 (1 hour)
   - Maximum: 86400 (24 hours)
   - Recommended: 3600-7200

3. Click **Save Settings**

### Step 8: Add Search to Your Site

#### Method 1: Via Block Editor (Gutenberg)

1. Edit a page or post
2. Add a **Shortcode** block
3. Enter: `[box_ai_search]`
4. Update/Publish

#### Method 2: Via Classic Editor

1. Edit a page or post
2. Add this shortcode where you want search to appear:
   ```
   [box_ai_search]
   ```
3. Update/Publish

#### Method 3: Via PHP Template

Add to your theme template:

```php
<?php echo do_shortcode( '[box_ai_search]' ); ?>
```

#### Method 4: Via Widget (Classic Widgets)

1. Go to **Appearance > Widgets**
2. Add **Shortcode** widget
3. Enter: `[box_ai_search]`
4. Save

### Step 9: Test Search

1. Visit the page where you added the shortcode
2. Enter a search query (at least 3 characters)
3. Verify results appear

## Troubleshooting

### Error: "Encryption is not available"

**Cause**: OpenSSL extension not installed or encryption key not defined

**Solutions**:
1. Check if OpenSSL is installed:
   ```bash
   php -m | grep openssl
   ```

2. Install OpenSSL:
   ```bash
   # Ubuntu/Debian
   sudo apt-get install php-openssl

   # CentOS/RHEL
   sudo yum install php-openssl
   ```

3. Verify encryption key in wp-config.php:
   ```php
   define( 'BAS_ENCRYPTION_KEY', 'your-key-here' );
   ```

4. Restart web server:
   ```bash
   sudo service apache2 restart
   # or
   sudo service nginx restart
   ```

### Error: "Connection test failed"

**Cause**: Invalid credentials or network issues

**Solutions**:

1. **Verify Credentials**
   - Double-check Client ID and Secret
   - Ensure no extra spaces or newlines
   - Verify Enterprise ID is correct

2. **Check Box App Status**
   - Go to Box Developer Console
   - Verify app is authorized
   - Check app scopes are correct

3. **Network Issues**
   - Verify server can reach api.box.com:
     ```bash
     curl -I https://api.box.com
     ```
   - Check firewall rules
   - Verify proxy settings if applicable

4. **JWT Issues** (if using JWT)
   - Verify private key format
   - Check passphrase is correct
   - Ensure public key is added to Box app

### Error: "Failed to decrypt Box API credentials"

**Cause**: Encryption key changed or corrupted

**Solutions**:
1. Re-enter Box credentials in settings
2. Verify encryption key in wp-config.php
3. Don't change the encryption key after saving credentials

### Search Returns No Results

**Cause**: Various possible issues

**Solutions**:

1. **Verify Box Content**
   - Ensure Box account has accessible documents
   - Check user has permissions to view content

2. **Check Box AI Status**
   - Verify Box AI is enabled for enterprise
   - Contact Box support if needed

3. **Clear Cache**
   - Click **Clear All Cached Results** in settings
   - Try search again

4. **Check Query**
   - Minimum 3 characters required
   - Try different search terms

5. **Check Error Logs**
   - WordPress debug.log
   - PHP error_log
   - Browser console

### Cache Issues

**Problem**: Stale results or cache not working

**Solutions**:

1. **Clear Plugin Cache**
   - Go to Settings > Box AI Search
   - Click **Clear All Cached Results**

2. **Check Object Cache**
   - Verify if object cache is active
   - Install Redis or Memcached for better performance

3. **Verify Transients**
   - Check database for `_transient_bas_cache_*` entries
   - Clear with WP-CLI:
     ```bash
     wp transient delete --all
     ```

## Performance Optimization

### Enable Object Caching

For best performance, use object caching:

#### Redis (Recommended)

1. Install Redis:
   ```bash
   sudo apt-get install redis-server php-redis
   ```

2. Install WordPress Redis plugin:
   ```bash
   wp plugin install redis-cache --activate
   wp redis enable
   ```

#### Memcached

1. Install Memcached:
   ```bash
   sudo apt-get install memcached php-memcached
   ```

2. Add to wp-config.php:
   ```php
   define( 'WP_CACHE', true );
   ```

### Optimize Cache Settings

- **High Traffic**: 7200 seconds (2 hours)
- **Medium Traffic**: 3600 seconds (1 hour)
- **Frequent Updates**: 1800 seconds (30 minutes)

## Security Hardening

### Protect wp-config.php

```bash
# Set restrictive permissions
chmod 400 wp-config.php

# Or for some setups
chmod 440 wp-config.php

# Verify
ls -l wp-config.php
```

### Add to .htaccess

Prevent access to wp-config.php:

```apache
<files wp-config.php>
order allow,deny
deny from all
</files>
```

### Regular Backups

Backup your encryption key and credentials:
1. Save encryption key to password manager
2. Backup Box app credentials
3. Export plugin settings periodically

## Uninstallation

### Complete Removal

1. **Deactivate Plugin**
   ```bash
   wp plugin deactivate box-ai-search
   ```

2. **Delete Plugin**
   ```bash
   wp plugin delete box-ai-search
   ```

   Or via WordPress admin:
   - Go to **Plugins**
   - Click **Delete** under Box AI Search

3. **Remove from wp-config.php** (optional)
   - Delete the `BAS_ENCRYPTION_KEY` line

Note: Uninstalling automatically removes:
- All plugin options
- Cached search results
- Stored credentials
- Transients

## Support

For additional help:

- **Documentation**: See README.md
- **Box AI Docs**: https://developer.box.com/guides/box-ai/
- **WordPress Support**: https://wordpress.org/support/
- **GitHub Issues**: https://github.com/jyoung2000/boxwp/issues

## Next Steps

After installation:

1. ✅ Test search functionality
2. ✅ Monitor performance
3. ✅ Set up regular backups
4. ✅ Configure caching for your needs
5. ✅ Customize search interface (CSS)
6. ✅ Review security settings

---

**Installation Complete!** 🎉

Your WordPress site now has Box AI-powered semantic search.
