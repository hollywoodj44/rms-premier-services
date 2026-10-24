<?php
/**
 * RMS Premier Services Theme Functions and Definitions
 *
 * @package RMS_Premier_Services
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('RMS_THEME_VERSION', '1.0.0');
define('RMS_THEME_DIR', get_template_directory());
define('RMS_THEME_URI', get_template_directory_uri());

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function rms_theme_setup() {
    // Add default title tag support
    add_theme_support('title-tag');

    // Add post-thumbnails support
    add_theme_support('post-thumbnails');

    // Add HTML5 markup support
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'rms-premier-services'),
        'footer'  => __('Footer Navigation', 'rms-premier-services'),
    ));
}
add_action('after_setup_theme', 'rms_theme_setup');

/**
 * Enqueue scripts and styles.
 */
function rms_enqueue_assets() {
    // Enqueue Google Fonts (Inter and Outfit)
    wp_enqueue_style(
        'rms-google-fonts',
        'https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&family=Outfit:wght@300;400;600;700;800;900&display=swap',
        array(),
        null
    );

    // Enqueue Main Theme Stylesheet
    wp_enqueue_style(
        'rms-theme-style',
        get_stylesheet_uri(),
        array('rms-google-fonts'),
        RMS_THEME_VERSION
    );

    // Enqueue Interactive Theme Script
    wp_enqueue_script(
        'rms-theme-script',
        RMS_THEME_URI . '/assets/js/main.js',
        array(),
        RMS_THEME_VERSION,
        true
    );

    // Pass AJAX endpoint and security nonce to JavaScript
    wp_localize_script('rms-theme-script', 'rmsData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('rms_consultation_nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'rms_enqueue_assets');

/**
 * Helper to get the primary consultation recipient email.
 */
function rms_get_contact_email() {
    $email = get_option('rms_consultation_email');
    if (empty($email) || !is_email($email)) {
        return 'sthomas@rmspremierservices.com';
    }
    return sanitize_email($email);
}

/**
 * Include the consultation email and inquiry management module.
 */
require_once RMS_THEME_DIR . '/inc/consultation-handler.php';
