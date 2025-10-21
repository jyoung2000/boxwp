# Build Instructions

This document explains how to create a production-ready ZIP file for installing the Box AI Search plugin in WordPress.

## Quick Start

### Linux / macOS

```bash
./quick-build.sh
```

The ZIP file will be created in `dist/box-ai-search-{version}.zip`

### Windows

```batch
build.bat
```

The ZIP file will be created in `dist\box-ai-search-{version}.zip`

## Build Scripts

### 1. Full Build Script (Linux/macOS)

**File**: `build.sh`

```bash
chmod +x build.sh
./build.sh
```

**Features**:
- Colored output
- Detailed progress messages
- File size reporting
- Optional cleanup prompt
- Installation instructions

### 2. Quick Build Script (Linux/macOS)

**File**: `quick-build.sh`

```bash
chmod +x quick-build.sh
./quick-build.sh
```

**Features**:
- Fast execution
- No prompts
- Auto-cleanup
- Minimal output

### 3. Windows Build Script

**File**: `build.bat`

```batch
build.bat
```

**Features**:
- PowerShell compression
- Interactive cleanup
- Windows-formatted output
- Installation instructions

## Manual Build Process

If you prefer to build manually:

### Using Command Line

```bash
# Navigate to plugin directory
cd box-ai-search

# Create directories
mkdir -p build/box-ai-search
mkdir -p dist

# Copy files
cp box-ai-search.php build/box-ai-search/
cp uninstall.php build/box-ai-search/
cp -r admin includes public build/box-ai-search/
cp README.md INSTALL.md SECURITY.md CHANGELOG.md build/box-ai-search/
mkdir build/box-ai-search/languages

# Create ZIP
cd build
zip -r ../dist/box-ai-search.zip box-ai-search
cd ..

# Cleanup
rm -rf build
```

### Using GUI Tools

1. **Create a folder** named `box-ai-search`

2. **Copy these files** into the folder:
   - `box-ai-search.php`
   - `uninstall.php`
   - `README.md`
   - `INSTALL.md`
   - `SECURITY.md`
   - `CHANGELOG.md`

3. **Copy these directories** into the folder:
   - `admin/`
   - `includes/`
   - `public/`

4. **Create empty directory**:
   - `languages/`

5. **Right-click the folder** and select "Compress" or "Send to ZIP"

6. **Rename the ZIP** to `box-ai-search-1.0.0.zip` (or current version)

## What Gets Included

The build process includes:

### Required Files
- ✅ `box-ai-search.php` - Main plugin file
- ✅ `uninstall.php` - Uninstall handler
- ✅ `admin/` - Admin interface and settings
- ✅ `includes/` - Core plugin classes
- ✅ `public/` - Frontend assets and shortcode
- ✅ `languages/` - Translation files directory

### Documentation
- ✅ `README.md` - Plugin documentation
- ✅ `INSTALL.md` - Installation guide
- ✅ `SECURITY.md` - Security policy
- ✅ `CHANGELOG.md` - Version history

## What Gets Excluded

The build process excludes:

### Development Files
- ❌ `tests/` - PHPUnit tests
- ❌ `vendor/` - Composer dependencies
- ❌ `node_modules/` - npm dependencies
- ❌ `phpunit.xml` - PHPUnit configuration
- ❌ `composer.json` - Composer configuration
- ❌ `.git/` - Git repository
- ❌ `.gitignore` - Git ignore file

### Build Artifacts
- ❌ `build/` - Build directory
- ❌ `dist/` - Distribution directory
- ❌ `*.log` - Log files

### System Files
- ❌ `.DS_Store` - macOS metadata
- ❌ `Thumbs.db` - Windows thumbnails
- ❌ `.idea/` - IDE settings
- ❌ `.vscode/` - VS Code settings

## Installing the Built Plugin

### Method 1: WordPress Admin Panel

1. Go to **Plugins > Add New**
2. Click **Upload Plugin**
3. Choose `dist/box-ai-search-{version}.zip`
4. Click **Install Now**
5. Click **Activate Plugin**

### Method 2: FTP/SFTP Upload

1. Extract `box-ai-search-{version}.zip`
2. Upload the `box-ai-search` folder to `/wp-content/plugins/`
3. Go to **Plugins** in WordPress admin
4. Find "Box AI Search"
5. Click **Activate**

### Method 3: WP-CLI

```bash
wp plugin install /path/to/box-ai-search-{version}.zip --activate
```

## Verifying the Build

### Check ZIP Structure

```bash
# Extract and view structure
unzip -l dist/box-ai-search-1.0.0.zip

# Expected structure:
# box-ai-search/
# ├── box-ai-search.php
# ├── uninstall.php
# ├── admin/
# ├── includes/
# ├── public/
# ├── languages/
# └── *.md files
```

### Check File Count

The ZIP should contain approximately:
- **22 files** (PHP, CSS, JS, MD)
- **3 main directories** (admin, includes, public)
- **1 empty directory** (languages)

### Check Plugin Header

Extract and verify `box-ai-search.php` contains:

```php
/**
 * Plugin Name:       Box AI Search
 * Plugin URI:        https://github.com/jyoung2000/boxwp
 * Description:       Integrate Box AI's semantic document search...
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.0
 * ...
 */
```

## Troubleshooting

### "Command not found: zip"

**Linux**:
```bash
sudo apt-get install zip unzip
```

**macOS**:
```bash
brew install zip
```

**Windows**: Use PowerShell (built-in) or install 7-Zip

### Permission Denied

```bash
chmod +x build.sh
chmod +x quick-build.sh
```

### PowerShell Execution Policy (Windows)

If build.bat fails with PowerShell errors:

```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### ZIP File Too Large

The ZIP should be under 1MB. If larger, check for:
- Accidentally included `vendor/` or `node_modules/`
- Large log files
- Test data

### Invalid Plugin Header

Ensure `box-ai-search.php` is in the root of the plugin folder, not nested deeper.

## Build Automation

### GitHub Actions

Create `.github/workflows/build.yml`:

```yaml
name: Build Plugin

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Build plugin
        run: |
          cd box-ai-search
          ./quick-build.sh
      - name: Upload artifact
        uses: actions/upload-artifact@v2
        with:
          name: box-ai-search
          path: box-ai-search/dist/*.zip
```

### Local Git Hook

Create `.git/hooks/pre-push`:

```bash
#!/bin/bash
cd box-ai-search
./quick-build.sh
echo "✓ Plugin built successfully"
```

## Release Checklist

Before building a release:

- [ ] Update version in `box-ai-search.php`
- [ ] Update version in `CHANGELOG.md`
- [ ] Update `README.md` with new features
- [ ] Run tests: `vendor/bin/phpunit`
- [ ] Test on clean WordPress install
- [ ] Build ZIP file
- [ ] Test ZIP installation
- [ ] Tag release in Git
- [ ] Upload to WordPress.org (if applicable)

## Distribution

### WordPress.org

To submit to WordPress.org plugin repository:

1. Create account at https://wordpress.org/
2. Submit plugin: https://wordpress.org/plugins/developers/add/
3. Wait for review (2-14 days)
4. Use SVN for updates (instructions provided after approval)

### Private Distribution

For internal/private distribution:

1. Build ZIP file
2. Upload to secure server
3. Share download link with authorized users
4. Consider update mechanism (GitHub releases, custom updater)

### GitHub Releases

1. Build ZIP file
2. Create GitHub release
3. Upload ZIP as release asset
4. Users can download from releases page

## Version Management

Update version numbers in:

1. `box-ai-search.php` - Plugin header
2. `CHANGELOG.md` - Version history
3. `README.md` - Upgrade notice (if applicable)

Use semantic versioning: `MAJOR.MINOR.PATCH`

## Support

For build issues:
- Check this document first
- Review build script output
- Check file permissions
- Verify folder structure

---

**Ready to Build?** Run `./quick-build.sh` or `build.bat` to get started!
