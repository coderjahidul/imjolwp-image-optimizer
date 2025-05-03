<?php
/**
 * Plugin Name: IMJOLWP Image Optimizer
 * Plugin URI:  https://github.com/coderjahidul/imjolwp-image-optimizer
 * Description: Automatically converts uploaded images to WebP without changing the URL for improved performance.
 * Version:     1.3
 * Author:      MD Jahidul Islam Sabuz
 * Author URI:  https://github.com/coderjahidul/
 * License:     GPL2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Add Admin Menu for Settings
add_action('admin_menu', 'imjolwp_webp_optimizer_menu');

function imjolwp_webp_optimizer_menu() {
    add_options_page(
        'Imjolwp Image Optimizer Settings',
        'Imjolwp Image Optimizer',
        'manage_options',
        'imjolwp-webp-optimizer',
        'imjolwp_webp_optimizer_settings_page'
    );
}

add_action('admin_init', 'imjolwp_register_webp_settings');

function imjolwp_register_webp_settings() {
    // Sanitize as an integer between 0-100 (WebP quality)
    register_setting(
        'imjolwp_webp_optimizer_settings',
        'imjolwp_webp_quality',
        array(
            'type'              => 'integer',
            'sanitize_callback' => 'imjolwp_sanitize_quality',
            'default'           => 80,
        )
    );

    // Sanitize as boolean (remove metadata checkbox)
    register_setting(
        'imjolwp_webp_optimizer_settings',
        'imjolwp_remove_metadata',
        array(
            'type'              => 'boolean',
            'sanitize_callback' => 'imjolwp_sanitize_boolean',
            'default'           => 1,
        )
    );
}

// Sanitize as an integer between 0-100
function imjolwp_sanitize_quality( $value ) {
    $value = intval( $value );
    return ($value >= 0 && $value <= 100) ? $value : 80;
}

// Sanitize as boolean
function imjolwp_sanitize_boolean( $value ) {
    return ( $value === '1' || $value === 1 || $value === true || $value === 'true' ) ? 1 : 0;
}


// Settings Page
function imjolwp_webp_optimizer_settings_page() {
    ?>
    <div class="wrap">
        <h2>Imjolwp Image Optimizer Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('imjolwp_webp_optimizer_settings');
            do_settings_sections('imjolwp_webp_optimizer_settings');
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="imjolwp_webp_quality">WebP Quality (0-100)</label></th>
                    <td><input type="number" name="imjolwp_webp_quality" id="imjolwp_webp_quality" value="<?php echo esc_attr(get_option('imjolwp_webp_quality', 80)); ?>" min="10" max="100"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="imjolwp_remove_metadata">Remove Metadata</label></th>
                    <td>
                        <input type="checkbox" name="imjolwp_remove_metadata" id="imjolwp_remove_metadata" value="1" <?php checked(1, get_option('imjolwp_remove_metadata', 1)); ?>>
                        <label for="imjolwp_remove_metadata">Enable Metadata Removal</label>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Hook into upload process
add_filter('wp_handle_upload', 'imjolwp_convert_to_webp');

function imjolwp_convert_to_webp($upload) {
    $file_path = $upload['file'];
    $converted_path = imjolwp_generate_webp($file_path);

    if ($converted_path) {
        error_log("Converted to WebP: " . $converted_path);
    } else {
        error_log("Failed to convert to WebP: " . $file_path);
    }

    return $upload;
}

// Convert Image to WebP Format
function imjolwp_generate_webp($file_path) {
    global $wp_filesystem;

    // Initialize WordPress Filesystem API
    if (empty($wp_filesystem)) {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
    }

    $webp_quality = get_option('imjolwp_webp_quality', 80);
    $remove_metadata = get_option('imjolwp_remove_metadata', 1);

    $info = getimagesize($file_path);
    $mime = $info['mime'];
    $webp_path = $file_path . '.webp';

    if (class_exists('Imagick')) {
        try {
            $image = new Imagick($file_path);
            $image->setImageFormat('webp');
            $image->setImageCompressionQuality($webp_quality);

            if ($remove_metadata) {
                $image->stripImage(); // Remove metadata
            }

            $image->writeImage($webp_path);
            $image->destroy();

            if ($wp_filesystem->move($webp_path, $file_path, true)) {
                return $file_path;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    } elseif (function_exists('imagewebp')) {
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng