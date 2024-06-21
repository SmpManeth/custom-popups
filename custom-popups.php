<?php

/**
 * Plugin Name: Custom Popups for Wordpress
 * Description: A plugin to create and manage popups on the homepage.This plugin allows you to create and manage popups on the homepage of your WordPress website.
 * Version: 1.0.0
 * Author: Maneth Pathirana
 * Plugin URI: https://wordroids.com/plugins/custom-popups
 * Author URI: https://github.com/SmpManeth?tab=repositories
 * Text Domain: custom-popups
 * Domain Path: /languages
 * 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Tested up to: 5.8
 * Stable tag: 1.0
 * Tags: popups, custom, homepage
 * 
 * This plugin allows you to create and manage popups on the homepage of your WordPress website.
 * It provides a custom post type for creating popups, along with additional fields and settings.
 * You can activate or deactivate individual popups, and display active popups on the homepage.
 * Custom styles and scripts are added to enhance the appearance and functionality of the popups.
 * 
 * @package Custom_Popups
 */

 if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Include the main class file
require_once plugin_dir_path(__FILE__) . 'includes/class-custom-popups.php';

// Initialize the plugin
function custom_popups_init() {
    $custom_popups = new Custom_Popups();
    $custom_popups->run();
}
add_action('plugins_loaded', 'custom_popups_init');


// Enqueue the styles and scripts for the admin
function custom_popups_enqueue_admin_assets() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_style('custom-popups-css', plugins_url('assets/css/custom-popups.css', __FILE__));
    wp_enqueue_script('custom-popups-js', plugins_url('assets/js/custom-popups.js', __FILE__), array('jquery', 'wp-color-picker'), false, true);
    
    // Add inline script to pass AJAX URL to the JavaScript file
    $ajaxurl = admin_url('admin-ajax.php');
    $script = 'var ajaxurl = "' . esc_url($ajaxurl) . '";';
    wp_add_inline_script('custom-popups-js', $script, 'before');
}
add_action('admin_enqueue_scripts', 'custom_popups_enqueue_admin_assets');

// Enqueue the styles and scripts for the frontend
function custom_popups_enqueue_frontend_assets() {
    wp_enqueue_style('custom-popups-css', plugins_url('assets/css/custom-popups.css', __FILE__));
    wp_enqueue_script('custom-popups-js', plugins_url('assets/js/custom-popups.js', __FILE__), array('jquery'), false, true);
}
add_action('wp_enqueue_scripts', 'custom_popups_enqueue_frontend_assets');

// Enable automatic updates for this plugin
add_filter('auto_update_plugin', 'custom_popups_auto_update', 10, 2);
function custom_popups_auto_update($update, $item) {
    if (isset($item->slug) && $item->slug === 'custom-popups') {
        return true; // Enable automatic updates
    }
    return $update;
}