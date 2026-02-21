# Build Script for AccountGo Update Package
Write-Host "Starting Build Process..." -ForegroundColor Green

# 1. Build Frontend Assets
Write-Host "Building Frontend Assets (Vite)..." -ForegroundColor Cyan
# In PowerShell, we can just run the command. 
# We use cmd /c to ensure .cmd/.bat shims are handled correctly if strict execution policies are in place, 
# but usually 'npm' works. We'll use strict checking.
npm run build
if ($LASTEXITCODE -ne 0) {
    Write-Host "Frontend build failed!" -ForegroundColor Red
    exit 1
}

# 1.5. Ensure PHP is in PATH
Write-Host "Checking PHP availability..." -ForegroundColor Cyan
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    # Attempt to find PHP in Laragon
    $phpPaths = @(
        "E:\laragon\bin\php",
        "C:\laragon\bin\php"
    )
    
    foreach ($basePath in $phpPaths) {
        if (Test-Path $basePath) {
            # value specific version or just pick the first directory that looks like php
            $phpDir = Get-ChildItem -Path $basePath -Directory | Select-Object -First 1
            if ($phpDir) {
                Write-Host "Found PHP at: $($phpDir.FullName)" -ForegroundColor Gray
                $env:Path = "$($phpDir.FullName);$env:Path"
                break
            }
        }
    }
}

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "PHP not found. Please ensure PHP is installed and in your PATH, or Laragon is running." -ForegroundColor Red
    exit 1
}

# 2. Optimize PHP Dependencies (Remove Dev)
Write-Host "Optimizing Composer Dependencies..." -ForegroundColor Cyan

# Attempt to find composer
$composer = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composer) {
    # Check common Laragon paths
    $possiblePaths = @(
        "E:\laragon\bin\composer\composer.bat",
        "C:\laragon\bin\composer\composer.bat"
    )
    foreach ($path in $possiblePaths) {
        if (Test-Path $path) {
            $composer = $path
            break
        }
    }
}

if (-not $composer) {
    Write-Host "Composer not found in PATH or standard Laragon locations." -ForegroundColor Red
    exit 1
}

Write-Host "Using Composer at: $composer" -ForegroundColor Gray

# Use --no-scripts to avoid pre/post install scripts that might fail (like pre-package-uninstall)
# causing "Concurrent process failed" errors when files are missing.
& $composer install --no-dev --no-scripts --no-progress
if ($LASTEXITCODE -ne 0) {
    Write-Host "Composer optimization failed!" -ForegroundColor Red
    exit 1
}

# Generate optimized autoloader explicitly
Write-Host "Generating Optimized Autoloader..." -ForegroundColor Cyan
& $composer dump-autoload --optimize --no-dev --no-scripts
if ($LASTEXITCODE -ne 0) {
    Write-Host "Autoloader generation failed!" -ForegroundColor Red
    exit 1
}

# 3. Define Zip Name
$date = Get-Date -Format "yyyy-MM-dd_HH-mm"
$zipName = "update_package_$date.zip"

Write-Host "Creating Zip Package: $zipName" -ForegroundColor Cyan

# 4. Create ZIP
# We need to exclude specific folders. Get-ChildItem with -Exclude operates on the immediate children names.
# To exclude nested 'node_modules', we need a more robust approach or just exclude the root folders of these names.

$excludeItems = @("node_modules", ".git", ".env", "storage", "tests", "*.zip", ".idea", ".vscode", "hot")

# Get all items in root, filter out exclusions
$filesToZip = Get-ChildItem -Path . -Exclude $excludeItems

# Powershell Compress-Archive can be slow for many files, but it allows exclusion more easily at the root level.
# Note: Compress-Archive takes a list of paths.
try {
    Compress-Archive -Path $filesToZip.FullName -DestinationPath $zipName -Force -ErrorAction Stop
} catch {
    Write-Host "Zip creation failed: $_" -ForegroundColor Red
    exit 1
}

if (Test-Path $zipName) {
    Write-Host "Update Package Created Successfully: $zipName" -ForegroundColor Green
} else {
    Write-Host "Failed to create zip file." -ForegroundColor Red
    exit 1
}

# 5. Restore Dev Dependencies
Write-Host "Restoring Dev Dependencies..." -ForegroundColor Cyan
& $composer install
if ($LASTEXITCODE -eq 0) {
    Write-Host "Done! You can now upload $zipName to your server." -ForegroundColor Green
} else {
    Write-Host "Warning: Failed to restore dev dependencies. Run 'composer install' manually." -ForegroundColor Yellow
}
