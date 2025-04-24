=== IMJOLWP Image Optimizer ===
Contributors: coderjahidul
Tags: webp, image optimizer, performance, image compression
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.0
Stable tag: 1.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically converts uploaded images to WebP without changing the image URL for improved performance.

== Description ==

**IMJOLWP Image Optimizer** automatically converts uploaded JPEG, PNG, or GIF images to WebP format, reducing image size and improving website performance. This is done without altering the image URL, maintaining backward compatibility.

**Features:**
* Converts JPEG, PNG, GIF to WebP
* Uses Imagick or GD depending on server support
* Admin settings for quality and metadata stripping
* Transparent URL replacement

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/imjolwp-image-optimizer`
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to **Settings > Imjolwp Image Optimizer** to configure

== Frequently Asked Questions ==

= Does it change image URLs? =
No, the original file path is preserved, only the content is replaced with WebP.

= What if my server doesn’t support Imagick? =
It will fall back to GD (imagewebp function) if Imagick is unavailable.

== Screenshots ==
1. Settings page with Imjolwp Image options

== Changelog ==

= 1.3 =
* Initial public release

== Upgrade Notice ==

= 1.3 =
First stable release.

