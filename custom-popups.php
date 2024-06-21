<?php

/**
 * Plugin Name: Custom Popups
 * Description: A plugin to create and manage popups on the homepage.
 * Version: 1.0
 * Author: Maneth
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Function to create a custom post type for popups
function create_popup_post_type()
{
    register_post_type(
        'popup',
        array(
            'labels'      => array(
                'name'          => __('Popups'),
                'singular_name' => __('Popup'),
            ),
            'public'      => true, // Make it publicly accessible
            'has_archive' => true, // Enable archive pages
            'supports'    => array('title', 'editor', 'thumbnail'), // Allow these features
            'show_in_rest' => true, // Enable Gutenberg editor support
        )
    );
}
add_action('init', 'create_popup_post_type'); // Hook into the 'init' action to register the custom post type

// Function to add meta boxes for additional fields
function add_popup_meta_boxes()
{
    add_meta_box(
        'popup_status_meta_box',
        'Popup Status',
        'render_popup_status_meta_box',
        'popup',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_popup_meta_boxes'); // Hook into 'add_meta_boxes' to add our custom meta box

// Function to render the popup status meta box
function render_popup_status_meta_box($post)
{
    $value = get_post_meta($post->ID, '_popup_status', true); // Retrieve current value of the meta key
?>
    <label for="popup_status">Activate Popup:</label>
    <input type="checkbox" name="popup_status" id="popup_status" <?php checked($value, 'on'); ?> /> <!-- Checkbox to activate/deactivate popup -->
<?php
}

// Function to save the meta box data
function save_popup_status_meta_box($post_id)
{
    if (array_key_exists('popup_status', $_POST)) {
        update_post_meta($post_id, '_popup_status', 'on'); // Save 'on' if checkbox is checked
    } else {
        update_post_meta($post_id, '_popup_status', 'off'); // Save 'off' if checkbox is unchecked
    }
}
add_action('save_post', 'save_popup_status_meta_box'); // Hook into 'save_post' to save our custom meta box data

// Function to display active popups on the homepage
function display_active_popups()
{
    $args = array(
        'post_type' => 'popup',
        'meta_key' => '_popup_status',
        'meta_value' => 'on' // Only get popups where the status is 'on'
    );
    $query = new WP_Query($args); // Query the database for the active popups

    if ($query->have_posts()) {
        echo '<div id="popup-overlay">';
        while ($query->have_posts()) {
            $query->the_post(); // Get the post data
            $background_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full'); // Get the URL of the featured image
?>
            <div class="popup" style="background-image: url('<?php echo esc_url($background_image_url); ?>');">
                <span class="popup-close">&times;</span> <!-- Close button -->
                <h2><?php the_title(); ?></h2> <!-- Display the title -->
                <div class="popup-content">
                    <?php the_content(); ?> <!-- Display the content -->
                </div>
            </div>
<?php
        }
        echo '</div>';
    }

    wp_reset_postdata(); // Reset post data after the custom query
}
add_action('wp_footer', 'display_active_popups'); // Hook into 'wp_footer' to display popups at the bottom of the page

// Function to add custom styles and scripts for the popups
function custom_popup_styles()
{
?>
    <style>
        #popup-overlay {
            display: none; /* Initially hide the overlay */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7); /* Semi-transparent background */
            z-index: 9999;
        }
        .popup {
            display: none; /* Initially hide the popup */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            max-height: 500px;
            overflow-y: auto; /* Allow scrolling if content exceeds max height */
            width: 80%;
            max-width: 500px;
            z-index: 10000;
            background-size: cover; /* Cover the background with the featured image */
            background-position: center;
            color: #fff; /* Ensure text is readable on background images */
        }
        .popup-content img {
            max-width: 100%;
        }
        .popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer; /* Pointer cursor for close button */
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var popups = document.querySelectorAll('.popup'); // Get all popup elements
            var overlay = document.getElementById('popup-overlay'); // Get the overlay element
            
            if (popups.length > 0) {
                overlay.style.display = 'block'; // Show the overlay if there are popups
            }

            popups.forEach(function(popup) {
                popup.style.display = 'block'; // Show each popup
                var closeBtn = popup.querySelector('.popup-close'); // Get the close button
                closeBtn.addEventListener('click', function() {
                    popup.style.display = 'none'; // Hide the popup when close button is clicked
                    overlay.style.display = 'none'; // Hide the overlay
                });
            });

            // Uncomment the following lines if you want to hide the popup when clicking outside
            //overlay.addEventListener('click', function() {
            //    popups.forEach(function(popup) {
            //        popup.style.display = 'none';
            //    });
            //    overlay.style.display = 'none';
            // });
        });
    </script>
<?php
}
add_action('wp_head', 'custom_popup_styles'); // Hook into 'wp_head' to add styles and scripts in the head section

?>
