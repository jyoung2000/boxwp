#!/bin/bash
#
# Build Script for Box AI Search WordPress Plugin
#
# Creates a production-ready ZIP file for WordPress plugin installation.
# Usage: ./build.sh
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuration
PLUGIN_SLUG="box-ai-search"
VERSION=$(grep "^ \* Version:" box-ai-search.php | awk '{print $3}')
BUILD_DIR="build"
DIST_DIR="dist"
ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"

echo -e "${GREEN}Building Box AI Search Plugin v${VERSION}${NC}"
echo "================================================"

# Clean previous builds
echo -e "\n${YELLOW}Cleaning previous builds...${NC}"
rm -rf "${BUILD_DIR}"
rm -rf "${DIST_DIR}"
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"
mkdir -p "${DIST_DIR}"

# Copy plugin files
echo -e "\n${YELLOW}Copying plugin files...${NC}"

# Copy main plugin file
cp box-ai-search.php "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Copy uninstall file
cp uninstall.php "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Copy directories
cp -r admin "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r includes "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp -r public "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Copy documentation
cp README.md "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp INSTALL.md "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp SECURITY.md "${BUILD_DIR}/${PLUGIN_SLUG}/"
cp CHANGELOG.md "${BUILD_DIR}/${PLUGIN_SLUG}/"

# Create languages directory
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}/languages"
echo "# Translation files go here" > "${BUILD_DIR}/${PLUGIN_SLUG}/languages/.gitkeep"

# Clean up development files from build
echo -e "\n${YELLOW}Removing development files...${NC}"
find "${BUILD_DIR}" -name "*.md" -type f ! -name "README.md" ! -name "INSTALL.md" ! -name "SECURITY.md" ! -name "CHANGELOG.md" -delete
find "${BUILD_DIR}" -name ".DS_Store" -delete
find "${BUILD_DIR}" -name "Thumbs.db" -delete
find "${BUILD_DIR}" -name ".git*" -delete

# Create ZIP file
echo -e "\n${YELLOW}Creating ZIP archive...${NC}"
cd "${BUILD_DIR}"
zip -r "../${DIST_DIR}/${ZIP_NAME}" "${PLUGIN_SLUG}" -q
cd ..

# Calculate file size
FILE_SIZE=$(du -h "${DIST_DIR}/${ZIP_NAME}" | cut -f1)

# Success message
echo -e "\n${GREEN}✓ Build completed successfully!${NC}"
echo "================================================"
echo -e "Plugin: ${GREEN}${PLUGIN_SLUG}${NC}"
echo -e "Version: ${GREEN}${VERSION}${NC}"
echo -e "File: ${GREEN}${DIST_DIR}/${ZIP_NAME}${NC}"
echo -e "Size: ${GREEN}${FILE_SIZE}${NC}"
echo ""
echo -e "${YELLOW}Installation Instructions:${NC}"
echo "1. Go to WordPress Admin > Plugins > Add New"
echo "2. Click 'Upload Plugin'"
echo "3. Choose: ${DIST_DIR}/${ZIP_NAME}"
echo "4. Click 'Install Now'"
echo "5. Activate the plugin"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "1. Add BAS_ENCRYPTION_KEY to wp-config.php"
echo "2. Configure Box API credentials"
echo "3. Test connection"
echo "4. Add [box_ai_search] shortcode to a page"
echo ""

# Cleanup option
read -p "Remove build directory? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    rm -rf "${BUILD_DIR}"
    echo -e "${GREEN}✓ Build directory removed${NC}"
fi

echo -e "\n${GREEN}Done!${NC}"
