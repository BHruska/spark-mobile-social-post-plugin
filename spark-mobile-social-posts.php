<?php
/**
 * Plugin Name: Spark Mobile Social Posts
 * Description: Mobile-friendly frontend form for creating Social Posts with camera integration
 * Version: 1.0.0
 * Author: Bth
 * License: GPL-2.0-or-later
 * Text Domain: spark-mobile-posts
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SPARK_MOBILE_PLUGIN_FILE', __FILE__ );
define( 'SPARK_MOBILE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SPARK_MOBILE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SPARK_MOBILE_PLUGIN_VER', '1.0.0' );

/**
 * Create the mobile submission page
 */
function spark_mobile_create_submission_page() {
    // Check if page already exists
    $page = get_page_by_path( 'mobile-social-post' );
    
    if ( ! $page ) {
        $page_data = array(
            'post_title'   => 'Create Social Post',
            'post_content' => '[spark_mobile_social_form]',
            'post_status'  => 'publish',
            'post_type'    => 'page',
            'post_name'    => 'mobile-social-post'
        );
        
        wp_insert_post( $page_data );
    }
}
register_activation_hook( __FILE__, 'spark_mobile_create_submission_page' );

/**
 * Add shortcode for the mobile form
 */
function spark_mobile_social_form_shortcode() {
    ob_start();
    spark_mobile_render_form();
    return ob_get_clean();
}
add_shortcode( 'spark_mobile_social_form', 'spark_mobile_social_form_shortcode' );

/**
 * Render the mobile-optimized form
 */
function spark_mobile_render_form() {
    // Handle form submission
    $message = '';
    if ( isset( $_POST['submit_social_post'] ) && wp_verify_nonce( $_POST['social_post_nonce'], 'submit_social_post' ) ) {
        $message = spark_mobile_process_submission();
    }
    
    // Get categories for dropdown
    $categories = get_terms( array(
        'taxonomy' => 'social-post-category',
        'hide_empty' => false,
    ) );
    ?>
    
    <div class="spark-mobile-form-container">
        <?php if ( $message ) : ?>
            <div class="spark-message <?php echo strpos( $message, 'Error' ) !== false ? 'error' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data" class="spark-mobile-form">
            <?php wp_nonce_field( 'submit_social_post', 'social_post_nonce' ); ?>
            
            <div class="form-group">
                <label for="post_title">Title</label>
                <input type="text" id="post_title" name="post_title" required 
                       placeholder="What's happening?" 
                       value="<?php echo isset( $_POST['post_title'] ) ? esc_attr( $_POST['post_title'] ) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="featured_image">Photo</label>
                <input type="file" id="featured_image" name="featured_image" 
                       accept="image/*" capture="environment" required>
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
                <button type="submit" name="submit_social_post" class="submit-btn">
                    Share Post
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
    
    .form-group input[type="file"] {
        width: 100%;
        padding: 16px;
        border: 2px dashed #007cba;
        border-radius: 8px;
        background: #f8f9fa;
        text-align: center;
        cursor: pointer;
        font-size: 16px;
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
    }
    </style>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('featured_image');
        const preview = document.getElementById('image_preview');
        
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
 * Process form submission
 */
function spark_mobile_process_submission() {
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
        'post_type'    => 'social-post',
        'post_author'  => get_current_user_id() ?: 1, // Use current user or admin
    );
    
    $post_id = wp_insert_post( $post_data );
    
    if ( is_wp_error( $post_id ) ) {
        return 'Error: Could not create post. Please try again.';
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
    wp_set_post_terms( $post_id, array( $category_id ), 'social-post-category' );
    
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
        echo '<div class="spark-message success">Your social post has been published successfully!</div>';
    }
}
add_action( 'wp_head', function() {
    if ( is_page( 'mobile-social-post' ) && isset( $_GET['posted'] ) ) {
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const container = document.querySelector(".spark-mobile-form-container");
            if (container) {
                container.insertAdjacentHTML("afterbegin", "<div class=\"spark-message success\">Your social post has been published successfully! <a href=\"" + window.location.pathname + "\">Create another post</a></div>");
            }
        });
        </script>';
    }
});

/**
 * Add viewport meta tag for mobile optimization
 */
function spark_mobile_add_viewport_meta() {
    if ( is_page( 'mobile-social-post' ) ) {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">';
    }
}
add_action( 'wp_head', 'spark_mobile_add_viewport_meta' );