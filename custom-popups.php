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
function create_popup_post_type()
{
    register_post_type(
        'popup',
        array(
            'labels'      => array(
                'name'          => __('Popups'),
                'singular_name' => __('Popup'),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
        )
    );
}
add_action('init', 'create_popup_post_type');

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
add_action('add_meta_boxes', 'add_popup_meta_boxes');

function render_popup_status_meta_box($post)
{
    $value = get_post_meta($post->ID, '_popup_status', true);
?>
    <label for="popup_status">Activate Popup:</label>
    <input type="checkbox" name="popup_status" id="popup_status" <?php checked($value, 'on'); ?> />
    <?php
}

function save_popup_status_meta_box($post_id)
{
    if (array_key_exists('popup_status', $_POST)) {
        update_post_meta($post_id, '_popup_status', 'on');
    } else {
        update_post_meta($post_id, '_popup_status', 'off');
    }
}
add_action('save_post', 'save_popup_status_meta_box');

function display_active_popups()
{
    $args = array(
        'post_type' => 'popup',
        'meta_key' => '_popup_status',
        'meta_value' => 'on'
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        echo '<div id="popup-overlay">';
        while ($query->have_posts()) {
            $query->the_post();
    ?>
            <div class="popup">
                <span class="popup-close">&times;</span>
                <h2><?php the_title(); ?></h2>
                <div class="popup-content">
                    <?php the_content(); ?>
                    <?php if (has_post_thumbnail()) {
                        the_post_thumbnail();
                    } ?>
                </div>
            </div>
    <?php
        }
        echo '</div>';
    }

    wp_reset_postdata();
}
add_action('wp_footer', 'display_active_popups');

function custom_popup_styles()
{
    ?>
<style>
        #popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 9999;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
            max-height: 500px;
            overflow-y: auto;
            width: 80%;
            max-width: 500px;
            z-index: 10000;
        }
        .popup-content img {
            max-width: 100%;
        }
        .popup-close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var popups = document.querySelectorAll('.popup');
            var overlay = document.getElementById('popup-overlay');
            
            if (popups.length > 0) {
                overlay.style.display = 'block';
            }

            popups.forEach(function(popup) {
                popup.style.display = 'block';
                var closeBtn = popup.querySelector('.popup-close');
                closeBtn.addEventListener('click', function() {
                    popup.style.display = 'none';
                    overlay.style.display = 'none';
                });
            });

            overlay.addEventListener('click', function() {
                popups.forEach(function(popup) {
                    popup.style.display = 'none';
                });
                overlay.style.display = 'none';
            });
        });
    </script>
<?php
}
add_action('wp_head', 'custom_popup_styles');
