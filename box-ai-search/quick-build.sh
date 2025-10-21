#!/bin/bash
#
# Quick Build Script - Creates ZIP without cleanup prompts
#
# Usage: ./quick-build.sh
#

set -e

PLUGIN_SLUG="box-ai-search"
VERSION=$(grep "^ \* Version:" box-ai-search.php | awk '{print $3}')
ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"

echo "Building ${PLUGIN_SLUG} v${VERSION}..."

# Clean and create directories
rm -rf build dist
mkdir -p build/${PLUGIN_SLUG} dist

# Copy files
cp box-ai-search.php uninstall.php build/${PLUGIN_SLUG}/
cp -r admin includes public build/${PLUGIN_SLUG}/
cp README.md INSTALL.md SECURITY.md CHANGELOG.md build/${PLUGIN_SLUG}/
mkdir -p build/${PLUGIN_SLUG}/languages

# Create ZIP
cd build
zip -r ../dist/${ZIP_NAME} ${PLUGIN_SLUG} -q
cd ..

# Cleanup
rm -rf build

echo "✓ Created: dist/${ZIP_NAME}"
echo ""
echo "Upload to WordPress: Plugins > Add New > Upload Plugin"
