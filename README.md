# README.md

# Spark Mobile Social Posts

A WordPress plugin that provides a mobile-friendly frontend form for creating Social Posts with camera integration.

## Features

- Mobile-optimized form interface
- Direct camera access for photo capture
- Integration with Social Post custom post type
- Category selection from Social Post Categories
- Automatic featured image setting
- Form validation and security
- Success/error messaging

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. A new page "Create Social Post" will be automatically created at `/mobile-social-post/`

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Social Post custom post type (from Spark Montessori Plugin)
- HTTPS (required for camera access on mobile)

## Usage

Share the URL `yoursite.com/mobile-social-post/` with users who need to create social posts from mobile devices. The form can be bookmarked on iPhone home screens for quick access.

## Form Fields

- **Title** (required): Post title
- **Photo** (required): Image upload with camera access
- **Category** (required): Social Post Category selection
- **Description** (optional): Post content

## Security Features

- WordPress nonces for CSRF protection
- File type validation (JPG, PNG, GIF only)
- File size limits (5MB maximum)
- Input sanitization
- Required field validation

## File Size and Type Limits

- Maximum file size: 5MB
- Allowed types: JPEG, JPG, PNG, GIF
- Images are automatically processed by WordPress media handler

## Browser Support

- iOS Safari (iPhone/iPad)
- Android Chrome
- Modern desktop browsers
- Camera access requires HTTPS

## Changelog

### 1.0.0

- Initial release
- Mobile form creation
- Camera integration
- Basic validation and security

## Zip it up commands

Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process

.\zip-plugin.ps1 -Force
