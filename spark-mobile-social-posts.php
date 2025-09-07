<?php
/**
 * Plugin Name: Spark Mobile Moments
 * Description: Mobile-friendly frontend form for creating Moments with camera integration
 * Version: 1.2.0
 * Author: Bth
 * License: GPL-2.0-or-later
 * Text Domain: spark-mobile-moments
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SPARK_MOBILE_PLUGIN_FILE', __FILE__ );
define( 'SPARK_MOBILE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPARK_MOBILE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SPARK_MOBILE_PLUGIN_VER', '1.2.0' );

/**
 * Create the mobile submission page
 */
function spark_mobile_create_submission_page() {
    // Check if page already exists
    $page = get_page_by_path( 'mobile-moment' );
    
    if ( ! $page ) {
        $page_data = array(
            'post_title'   => 'Create Moment',
            'post_content' => '[spark_mobile_moment_form]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_name'    => 'mobile-moment'
        );
        
        wp_insert_post( $page_data );
    }
}
register_activation_hook( __FILE__, 'spark_mobile_create_submission_page' );

/**
 * Add custom capability for moments
 */
function spark_mobile_add_capabilities() {
    // Add capability to administrator role
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->add_cap( 'create_moments' );
    }
    
    // Add capability to editor role
    $editor_role = get_role( 'editor' );
    if ( $editor_role ) {
        $editor_role->add_cap( 'create_moments' );
    }
    
    // Add capability to social_media_authority role
    $social_role = get_role( 'social_media_authority' );
    if ( $social_role ) {
        $social_role->add_cap( 'create_moments' );
    }
}
register_activation_hook( __FILE__, 'spark_mobile_add_capabilities' );

/**
 * Check if user has permission to create moments
 */
function spark_mobile_user_can_create_moments() {
    if ( ! is_user_logged_in() ) {
        return false;
    }
    
    return current_user_can( 'create_moments' ) || current_user_can( 'administrator' );
}

/**
 * Add shortcode for the mobile form
 */
function spark_mobile_moment_form_shortcode() {
    ob_start();
    spark_mobile_render_form();
    return ob_get_clean();
}
add_shortcode( 'spark_mobile_moment_form', 'spark_mobile_moment_form_shortcode' );

/**
 * Render the mobile-optimized form
 */
function spark_mobile_render_form() {
    // Check if user is logged in and has permission
    if ( ! spark_mobile_user_can_create_moments() ) {
        spark_mobile_render_login_notice();
        return;
    }
    
    // Handle form submission
    $message = '';
    if ( isset( $_POST['submit_moment'] ) && wp_verify_nonce( $_POST['moment_nonce'], 'submit_moment' ) ) {
        $message = spark_mobile_process_submission();
    }
    
    // Get categories for dropdown
    $categories = get_terms( array(
        'taxonomy' => 'moment-category',
        'hide_empty' => false,
    ) );
    
    $current_user = wp_get_current_user();
    $moments_archive_url = home_url( '/?post_type=moment' );
    ?>
    
    <div class="spark-mobile-form-container">
        <div class="user-info">
            <p>Welcome, <strong><?php echo esc_html( $current_user->display_name ); ?></strong>! 
            <a href="<?php echo wp_logout_url( get_permalink() ); ?>" class="logout-link">Logout</a></p>
        </div>
        
        <?php if ( $message ) : ?>
            <div class="spark-message <?php echo strpos( $message, 'Error' ) !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <?php if ( $moments_archive_url ) : ?>
                <button type="button" id="view_moments_btn" class="view-moments-btn" 
                        onclick="window.open('<?php echo esc_url( $moments_archive_url ); ?>', '_blank')">
                    👁️ View All Moments
                </button>
            <?php endif; ?>
        </div>
        
        <form method="post" enctype="multipart/form-data" class="spark-mobile-form">
            <?php wp_nonce_field( 'submit_moment', 'moment_nonce' ); ?>
            
            <div class="form-group">
                <label for="post_title">Title</label>
                <input type="text" id="post_title" name="post_title" required 
                       placeholder="What's happening?" 
                       value="<?php echo isset( $_POST['post_title'] ) ? esc_attr( $_POST['post_title'] ) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="featured_image">Photo</label>
                <div class="photo-options">
                    <button type="button" id="take_photo_btn" class="photo-btn">
                        📸 Take Photo
                    </button>
                    <button type="button" id="choose_photo_btn" class="photo-btn">
                        🖼️ Choose from Gallery
                    </button>
                </div>
                <input type="file" id="featured_image" name="featured_image" 
                       accept="image/*" required style="display: none;">
                <div id="image_preview"></div>
            </div>
            
            <div class="form-group">
                <label for="post_category">Category</label>
                <select id="post_category" name="post_category" required>
                    <option value="">Select a category...</option>
                    <?php foreach ( $categories as $category ) : ?>
                        <option value="<?php echo $category->term_id; ?>" 
                                <?php selected( isset( $_POST['post_category'] ) ? $_POST['post_category'] : '', $category->term_id ); ?>>
                            <?php echo esc_html( $category->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="post_content">Description</label>
                <textarea id="post_content" name="post_content" rows="4" 
                          placeholder="Tell us about this moment..."><?php echo isset( $_POST['post_content'] ) ? esc_textarea( $_POST['post_content'] ) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <button type="submit" name="submit_moment" class="submit-btn">
                    Share Moment
                </button>
            </div>
        </form>
    </div>
    
    <style>
    .spark-mobile-form-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .user-info {
        background: #f8f9fa;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .user-info p {
        margin: 0;
        font-size: 14px;
        color: #666;
    }
    
    .logout-link {
        color: #007cba;
        text-decoration: none;
        font-size: 14px;
    }
    
    .logout-link:hover {
        text-decoration: underline;
    }
    
    .action-buttons {
        margin-bottom: 20px;
        text-align: center;
    }
    
    .view-moments-btn {
        background: #28a745;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .view-moments-btn:hover {
        background: #218838;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
    }
    
    .view-moments-btn:active {
        transform: translateY(0);
    }
    
    .login-notice {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
    }
    
    .login-notice h3 {
        color: #856404;
        margin-top: 0;
    }
    
    .login-notice p {
        color: #856404;
        margin-bottom: 20px;
    }
    
    .login-btn {
        background: #007cba;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        display: inline-block;
        font-weight: 600;
    }
    
    .login-btn:hover {
        background: #005a87;
        color: white;
        text-decoration: none;
    }
    
    .spark-mobile-form {
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
        font-size: 16px;
    }
    
    .form-group input[type="text"],
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 16px;
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        font-size: 16px;
        transition: border-color 0.3s ease;
        box-sizing: border-box;
    }
    
    .form-group input[type="text"]:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: none;
        border-color: #007cba;
    }
    
    .photo-options {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }
    
    .photo-btn {
        flex: 1;
        padding: 16px 12px;
        background: #f8f9fa;
        border: 2px solid #007cba;
        border-radius: 8px;
        color: #007cba;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .photo-btn:hover {
        background: #007cba;
        color: white;
    }
    
    .photo-btn.active {
        background: #007cba;
        color: white;
    }
    
    #image_preview {
        margin-top: 12px;
        text-align: center;
    }
    
    #image_preview img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    
    .submit-btn {
        width: 100%;
        padding: 18px;
        background: #007cba;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .submit-btn:hover {
        background: #005a87;
    }
    
    .submit-btn:active {
        transform: translateY(1px);
    }
    
    .spark-message {
        padding: 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 500;
    }
    
    .spark-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .spark-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Mobile optimizations */
    @media (max-width: 768px) {
        .spark-mobile-form-container {
            padding: 12px;
        }
        
        .spark-mobile-form {
            padding: 16px;
        }
        
        .form-group input[type="text"],
        .form-group textarea,
        .form-group select {
            font-size: 16px; /* Prevents zoom on iOS */
        }
        
        .user-info {
            flex-direction: column;
            gap: 8px;
            text-align: center;
        }
        
        .photo-options {
            flex-direction: column;
        }
        
        .view-moments-btn {
            font-size: 14px;
            padding: 10px 20px;
        }
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('featured_image');
        const preview = document.getElementById('image_preview');
        const takePhotoBtn = document.getElementById('take_photo_btn');
        const choosePhotoBtn = document.getElementById('choose_photo_btn');
        
        // Handle photo selection buttons
        takePhotoBtn.addEventListener('click', function() {
            fileInput.setAttribute('capture', 'environment');
            fileInput.click();
            setActiveButton(takePhotoBtn);
        });
        
        choosePhotoBtn.addEventListener('click', function() {
            fileInput.removeAttribute('capture');
            fileInput.click();
            setActiveButton(choosePhotoBtn);
        });
        
        function setActiveButton(activeBtn) {
            [takePhotoBtn, choosePhotoBtn].forEach(btn => {
                btn.classList.remove('active');
            });
            activeBtn.classList.add('active');
        }
        
        // Handle file preview
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(file);
            }
        });
    });
    </script>
    
    <?php
}

/**
 * Render login notice for unauthorized users
 */
function spark_mobile_render_login_notice() {
    $login_url = wp_login_url( get_permalink() );
    ?>
    <div class="spark-mobile-form-container">
        <div class="login-notice">
            <h3>Access Required</h3>
            <p>You need to be logged in with appropriate permissions to create moments.</p>
            <p>Please log in to your WordPress admin account and ensure you have the "Create Moments" capability.</p>
            <a href="<?php echo esc_url( $login_url ); ?>" class="login-btn">Login to WordPress</a>
        </div>
    </div>
    
    <style>
    .spark-mobile-form-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .login-notice {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        padding: 30px 20px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    }
    
    .login-notice h3 {
        color: #856404;
        margin-top: 0;
        margin-bottom: 16px;
        font-size: 24px;
    }
    
    .login-notice p {
        color: #856404;
        margin-bottom: 16px;
        line-height: 1.5;
    }
    
    .login-btn {
        background: #007cba;
        color: white;
        padding: 16px 32px;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        font-weight: 600;
        font-size: 16px;
        transition: background 0.3s ease;
    }
    
    .login-btn:hover {
        background: #005a87;
        color: white;
        text-decoration: none;
    }
    </style>
    <?php
}

/**
 * Process form submission
 */
function spark_mobile_process_submission() {
    // Double-check permissions
    if ( ! spark_mobile_user_can_create_moments() ) {
        return 'Error: You do not have permission to create moments.';
    }
    
    // Validate required fields
    if ( empty( $_POST['post_title'] ) || empty( $_POST['post_category'] ) || empty( $_FILES['featured_image']['name'] ) ) {
        return 'Error: Please fill in all required fields.';
    }
    
    // Validate file upload
    if ( $_FILES['featured_image']['error'] !== UPLOAD_ERR_OK ) {
        return 'Error: There was a problem uploading your image.';
    }
    
    // Check file type
    $allowed_types = array( 'image/jpeg', 'image/jpg', 'image/png', 'image/gif' );
    if ( ! in_array( $_FILES['featured_image']['type'], $allowed_types ) ) {
        return 'Error: Please upload a valid image file (JPG, PNG, or GIF).';
    }
    
    // Check file size (5MB limit)
    if ( $_FILES['featured_image']['size'] > 5 * 1024 * 1024 ) {
        return 'Error: Image file is too large. Please use an image under 5MB.';
    }
    
    // Create the post
    $post_data = array(
        'post_title'   => sanitize_text_field( $_POST['post_title'] ),
        'post_content' => sanitize_textarea_field( $_POST['post_content'] ),
        'post_status'  => 'publish',
        'post_type'    => 'moment',
        'post_author'  => get_current_user_id(), // Use current logged-in user
    );
    
    $post_id = wp_insert_post( $post_data );
    
    if ( is_wp_error( $post_id ) ) {
        return 'Error: Could not create moment. Please try again.';
    }
    
    // Handle image upload
    require_once( ABSPATH . 'wp-admin/includes/image.php' );
    require_once( ABSPATH . 'wp-admin/includes/file.php' );
    require_once( ABSPATH . 'wp-admin/includes/media.php' );
    
    $attachment_id = media_handle_upload( 'featured_image', $post_id );
    
    if ( is_wp_error( $attachment_id ) ) {
        wp_delete_post( $post_id, true );
        return 'Error: Could not upload image. Please try again.';
    }
    
    // Set as featured image
    set_post_thumbnail( $post_id, $attachment_id );
    
    // Set category
    $category_id = intval( $_POST['post_category'] );
    wp_set_post_terms( $post_id, array( $category_id ), 'moment-category' );
    
    // Clear form by redirecting
    $redirect_url = add_query_arg( 'posted', '1', get_permalink() );
    wp_redirect( $redirect_url );
    exit;
}

/**
 * Show success message after redirect
 */
function spark_mobile_show_success_message() {
    if ( isset( $_GET['posted'] ) && $_GET['posted'] == '1' ) {
        echo '<div class="spark-message success">Your moment has been published successfully!</div>';
    }
}
add_action( 'wp_head', function() {
    if ( is_page( 'mobile-moment' ) && isset( $_GET['posted'] ) ) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.querySelector(".spark-mobile-form-container");
            if (container) {
                container.insertAdjacentHTML("afterbegin", "<div class=\"spark-message success\">Your moment has been published successfully! <a href=\"" + window.location.pathname + "\">Create another moment</a></div>");
            }
        });
        </script>';
    }
});

/**
 * Add viewport meta tag for mobile optimization
 */
function spark_mobile_add_viewport_meta() {
    if ( is_page( 'mobile-moment' ) ) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">';
    }
}
add_action( 'wp_head', 'spark_mobile_add_viewport_meta' );

/**
 * Admin menu for managing moment permissions
 */
function spark_mobile_admin_menu() {
    add_options_page(
        'Moments Permissions',
        'Moments',
        'manage_options',
        'spark-mobile-permissions',
        'spark_mobile_permissions_page'
    );
}
add_action( 'admin_menu', 'spark_mobile_admin_menu' );

/**
 * Admin page for managing permissions
 */
function spark_mobile_permissions_page() {
    if ( isset( $_POST['update_permissions'] ) && wp_verify_nonce( $_POST['permissions_nonce'], 'update_permissions' ) ) {
        $users = get_users();
        foreach ( $users as $user ) {
            $user_obj = new WP_User( $user->ID );
            if ( isset( $_POST['user_permissions'][ $user->ID ] ) ) {
                $user_obj->add_cap( 'create_moments' );
            } else {
                $user_obj->remove_cap( 'create_moments' );
            }
        }
        echo '<div class="notice notice-success"><p>Permissions updated successfully!</p></div>';
    }
    
    $users = get_users();
    ?>
    <div class="wrap">
        <h1>Moments Permissions</h1>
        <p>Manage which users can create moments from the mobile form.</p>
        
        <form method="post">
            <?php wp_nonce_field( 'update_permissions', 'permissions_nonce' ); ?>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Can Create Moments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $users as $user ) : ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html( $user->display_name ); ?></strong><br>
                                <small><?php echo esc_html( $user->user_email ); ?></small>
                            </td>
                            <td><?php echo esc_html( implode( ', ', $user->roles ) ); ?></td>
                            <td>
                                <input type="checkbox" name="user_permissions[<?php echo $user->ID; ?>]" 
                                       value="1" <?php checked( user_can( $user, 'create_moments' ) || user_can( $user, 'administrator' ) ); ?>
                                       <?php disabled( user_can( $user, 'administrator' ) ); ?>>
                                <?php if ( user_can( $user, 'administrator' ) ) : ?>
                                    <small>(Administrator - always has access)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="update_permissions" class="button-primary" value="Update Permissions">
            </p>
        </form>
        
        <div class="card">
            <h3>Quick Links</h3>
            <p><strong>Mobile Form URL:</strong> <a href="<?php echo get_permalink( get_page_by_path( 'mobile-moment' ) ); ?>" target="_blank"><?php echo get_permalink( get_page_by_path( 'mobile-moment' ) ); ?></a></p>
            <p><strong>Moments Archive:</strong> <a href="<?php echo get_post_type_archive_link( 'moment' ); ?>" target="_blank"><?php echo get_post_type_archive_link( 'moment' ); ?></a></p>
        </div>
    </div>
    <?php
}