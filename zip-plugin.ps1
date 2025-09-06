# Plugin Zip Creator Script
# Creates a clean zip file of your plugin, excluding development files

# Option 1: Allow for this session only
# Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process

# .\zip-plugin.ps1 -Force

param(
    [string]$SourcePath = ".",
    [string]$OutputPath = "",
    [switch]$Force
)

# If no output path specified, use current directory name and save one level up
if ([string]::IsNullOrEmpty($OutputPath)) {
    $currentDirName = Split-Path -Leaf (Get-Location)
    $parentDir = Split-Path -Parent (Get-Location)
    $OutputPath = Join-Path $parentDir "$currentDirName.zip"
}

# Files and directories to exclude (add or modify as needed)
$ExcludePatterns = @(
    ".git",
    ".gitignore",
    ".gitattributes",
    "node_modules",
    ".env",
    ".env.local",
    ".env.development",
    ".env.production",
    "*.log",
    "*.tmp",
    ".DS_Store",
    "Thumbs.db",
    ".vscode",
    ".idea",
    "*.bak",
    "*.swp",
    "*.swo",
    ".sass-cache",
    "dist",
    "build",
    "coverage",
    ".nyc_output",
    "package-lock.json",
    "yarn.lock",
    "composer.lock",
    "vendor",
    ".phpunit.result.cache",
    "__pycache__",
    "*.pyc",
    ".pytest_cache",
    "*.zip"
)

Write-Host "Creating plugin zip..." -ForegroundColor Green
Write-Host "Source: $SourcePath" -ForegroundColor Cyan
Write-Host "Output: $OutputPath" -ForegroundColor Cyan

# Remove existing zip file if Force parameter is used
if ($Force -and (Test-Path $OutputPath)) {
    Remove-Item $OutputPath -Force
    Write-Host "Removed existing zip file." -ForegroundColor Yellow
}

# Check if output file already exists
if (Test-Path $OutputPath) {
    $response = Read-Host "Output file '$OutputPath' already exists. Overwrite? (y/n)"
    if ($response -ne 'y') {
        Write-Host "Operation cancelled." -ForegroundColor Red
        exit 1
    }
    Remove-Item $OutputPath -Force
}

# Get all files in the source directory
$allFiles = Get-ChildItem -Path $SourcePath -Recurse -File

# Filter out excluded files
$filesToInclude = $allFiles | Where-Object {
    $file = $_
    $relativePath = $file.FullName.Substring($SourcePath.Length).TrimStart('\', '/')
    
    # Check if file matches any exclude pattern
    $shouldExclude = $false
    foreach ($pattern in $ExcludePatterns) {
        if ($relativePath -like "*$pattern*" -or $file.Name -like $pattern) {
            $shouldExclude = $true
            break
        }
    }
    
    return -not $shouldExclude
}

Write-Host "Found $($filesToInclude.Count) files to include" -ForegroundColor Cyan

# Create the zip file
try {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    
    $zip = [System.IO.Compression.ZipFile]::Open($OutputPath, 'Create')
    
    foreach ($file in $filesToInclude) {
        $relativePath = $file.FullName.Substring((Resolve-Path $SourcePath).Path.Length).TrimStart('\', '/')
        
        # Normalize path separators for zip file
        $zipEntryPath = $relativePath -replace '\\', '/'
        
        Write-Host "Adding: $zipEntryPath" -ForegroundColor Gray
        
        $zipEntry = $zip.CreateEntry($zipEntryPath)
        $zipEntryStream = $zipEntry.Open()
        $fileStream = [System.IO.File]::OpenRead($file.FullName)
        
        $fileStream.CopyTo($zipEntryStream)
        
        $fileStream.Close()
        $zipEntryStream.Close()
    }
    
    $zip.Dispose()
    
    $zipSize = (Get-Item $OutputPath).Length / 1MB
    Write-Host "`nPlugin zip created successfully!" -ForegroundColor Green
    Write-Host "File: $OutputPath" -ForegroundColor Cyan
    Write-Host "Size: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Cyan
    Write-Host "Files included: $($filesToInclude.Count)" -ForegroundColor Cyan
}
catch {
    Write-Host "Error creating zip file: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Show excluded files (optional - uncomment if you want to see what was excluded)
<#
Write-Host "`nExcluded files:" -ForegroundColor Yellow
$excludedFiles = $allFiles | Where-Object {
    $file = $_
    $relativePath = $file.FullName.Substring($SourcePath.Length).TrimStart('\', '/')
    
    $shouldExclude = $false
    foreach ($pattern in $ExcludePatterns) {
        if ($relativePath -like "*$pattern*" -or $file.Name -like $pattern) {
            $shouldExclude = $true
            break
        }
    }
    
    return $shouldExclude
}

foreach ($file in $excludedFiles) {
    $relativePath = $file.FullName.Substring($SourcePath.Length).TrimStart('\', '/')
    Write-Host "  $relativePath" -ForegroundColor DarkGray
}
#>