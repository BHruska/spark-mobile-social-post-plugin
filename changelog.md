# README.md

# Spark Mobile Moments

A WordPress plugin that provides a mobile-friendly frontend form for creating Moments with camera integration.

## Features

- Mobile-optimized form interface
- Direct camera access for photo capture
- Integration with Moment custom post type
- Category selection from Moment Categories
- Automatic featured image setting
- Form validation and security
- Success/error messaging
- "View Moments" button to browse existing moments
- Permission management system

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the WordPress admin
3. A new page "Create Moment" will be automatically created at `/mobile-moment/`

## Requirements

- WordPress 5.0+
- PHP 7.4+
- Moment custom post type (from Spark Montessori Plugin)
- HTTPS (required for camera access on mobile)

## Usage

Share the URL `yoursite.com/mobile-moment/` with users who need to create moments from mobile devices. The form can be bookmarked on iPhone home screens for quick access.

### Permission Management

Navigate to **WordPress Admin > Settings > Moments** to manage which users can create moments through the mobile form.

## Form Fields

- **Title** (required): Moment title
- **Photo** (required): Image upload with camera access
- **Category** (required): Moment Category selection
- **Description** (optional): Moment content

## Security Features

- WordPress nonces for CSRF protection
- File type validation (JPG, PNG, GIF only)
- File size limits (5MB maximum)
- Input sanitization
- Required field validation
- User capability checking

## File Size and Type Limits

- Maximum file size: 5MB
- Allowed types: JPEG, JPG, PNG, GIF
- Images are automatically processed by WordPress media handler

## Browser Support

- iOS Safari (iPhone/iPad)
- Android Chrome
- Modern desktop browsers
- Camera access requires HTTPS

## User Capabilities

The plugin creates a `create_moments` capability that can be assigned to users. By default:

- Administrators always have access
- Social Media Authority role automatically gets access
- Other users can be granted access via the admin interface

## Mobile Optimization

- Responsive design optimized for mobile devices
- Large touch targets for easy interaction
- Camera integration for quick photo capture
- Form prevents zoom on iOS devices
- Optimized loading and performance

## Zip it up commands

```powershell
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process
.\zip-plugin.ps1 -Force
```
