<?php
/**
 * Uninstall script for Spark Mobile Social Posts
 * 
 * This file is called when the plugin is deleted through WordPress admin.
 */

// If uninstall not called from WordPress, exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove the mobile social post page
$page = get_page_by_path( 'mobile-social-post' );
if ( $page ) {
    wp_delete_post( $page->ID, true );
}

// Clean up any options (if we add any in the future)
// delete_option( 'spark_mobile_option_name' );

// Note: We don't delete Social Posts or categories as they may be used by other plugins
