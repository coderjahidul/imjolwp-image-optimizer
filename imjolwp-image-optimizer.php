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
        'WebP Optimizer Settings',
        'WebP Optimizer',
        'manage_options',
        'imjolwp-webp-optimizer',
        'imjolwp_webp_optimizer_settings_page'
    );
}

// Register Settings
add_action('admin_init', 'imjolwp_register_webp_settings');

function imjolwp_register_webp_settings() {
    register_setting('imjolwp_webp_optimizer_settings', 'imjolwp_webp_quality', ['default' => 80]);
    register_setting('imjolwp_webp_optimizer_settings', 'imjolwp_remove_metadata', ['default' => 1]);
}

// Settings Page
function imjolwp_webp_optimizer_settings_page() {
    ?>
    <div class="wrap">
        <h2>WebP Optimizer Settings</h2>
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

            rename($webp_path, $file_path); // Replace original image with WebP
            return $file_path;
        } catch (Exception $e) {
            return false;
        }
    } elseif (function_exists('imagewebp')) {
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file_path);
                break;
            default:
                return false;
        }

        imagewebp($image, $webp_path, $webp_quality);
        imagedestroy($image);

        rename($webp_path, $file_path); // Replace original image with WebP
        return $file_path;
    }

    return false;
}