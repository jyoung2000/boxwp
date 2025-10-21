@echo off
REM Build Script for Box AI Search WordPress Plugin (Windows)
REM
REM Usage: build.bat
REM Requires: 7-Zip or PowerShell (for compression)

setlocal enabledelayedexpansion

REM Configuration
set PLUGIN_SLUG=box-ai-search
for /f "tokens=2" %%i in ('findstr "Version:" box-ai-search.php') do set VERSION=%%i
set ZIP_NAME=%PLUGIN_SLUG%-%VERSION%.zip

echo Building Box AI Search Plugin v%VERSION%
echo ================================================
echo.

REM Clean previous builds
echo Cleaning previous builds...
if exist build rmdir /s /q build
if exist dist rmdir /s /q dist
mkdir build\%PLUGIN_SLUG%
mkdir dist

REM Copy plugin files
echo Copying plugin files...
copy box-ai-search.php build\%PLUGIN_SLUG%\ >nul
copy uninstall.php build\%PLUGIN_SLUG%\ >nul
xcopy /E /I /Q admin build\%PLUGIN_SLUG%\admin >nul
xcopy /E /I /Q includes build\%PLUGIN_SLUG%\includes >nul
xcopy /E /I /Q public build\%PLUGIN_SLUG%\public >nul
copy README.md build\%PLUGIN_SLUG%\ >nul
copy INSTALL.md build\%PLUGIN_SLUG%\ >nul
copy SECURITY.md build\%PLUGIN_SLUG%\ >nul
copy CHANGELOG.md build\%PLUGIN_SLUG%\ >nul
mkdir build\%PLUGIN_SLUG%\languages

REM Create ZIP using PowerShell
echo Creating ZIP archive...
powershell -command "Compress-Archive -Path 'build\%PLUGIN_SLUG%' -DestinationPath 'dist\%ZIP_NAME%' -Force"

REM Check if ZIP was created
if exist dist\%ZIP_NAME% (
    echo.
    echo ================================================
    echo Build completed successfully!
    echo ================================================
    echo Plugin: %PLUGIN_SLUG%
    echo Version: %VERSION%
    echo File: dist\%ZIP_NAME%
    echo.
    echo Installation Instructions:
    echo 1. Go to WordPress Admin ^> Plugins ^> Add New
    echo 2. Click 'Upload Plugin'
    echo 3. Choose: dist\%ZIP_NAME%
    echo 4. Click 'Install Now'
    echo 5. Activate the plugin
    echo.

    REM Cleanup
    choice /C YN /M "Remove build directory"
    if errorlevel 2 goto :end
    if errorlevel 1 (
        rmdir /s /q build
        echo Build directory removed
    )
) else (
    echo ERROR: Failed to create ZIP file
    exit /b 1
)

:end
echo.
echo Done!
pause
