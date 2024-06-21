<?php

/**
 * Plugin Name: Custom Popups
 * Description: A plugin to create and manage popups on the homepage.This plugin allows you to create and manage popups on the homepage of your WordPress website.
 * Version: 1.0
 * Author: Maneth
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

//load teh css and js
function custom_popups_enqueue_scripts() {
    wp_enqueue_style('custom-popups', plugin_dir_url(__FILE__) . 'assets/css/custom-popups.css');
    wp_enqueue_script('custom-popups', plugin_dir_url(__FILE__) . 'assets/js/custom-popups.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'custom_popups_enqueue_scripts');

// // Register activation and deactivation hooks
// register_activation_hook(__FILE__, array('Custom_Popups', 'activate'));
// register_deactivation_hook(__FILE__, array('Custom_Popups', 'deactivate'));


// // Load the plugin text domain
// function custom_popups_load_textdomain() {
//     load_plugin_textdomain('custom-popups', false, dirname(plugin_basename(__FILE__)) . '/languages/');
// }

// add_action('plugins_loaded', 'custom_popups_load_textdomain');

// // Add a link to the settings page from the plugins page
// function custom_popups_add_settings_link($links) {
//     $settings_link = '<a href="admin.php?page=custom-popups">' . __('Settings', 'custom-popups') . '</a>';
//     array_unshift($links, $settings_link);
//     return $links;
// }

// $plugin = plugin_basename(__FILE__);

// add_filter("plugin_action_links_$plugin", 'custom_popups_add_settings_link');

// Add a link to the settings page from the plugins page