<?php

class Custom_Popups {

    public function run() {
        add_action('init', array($this, 'create_popup_post_type'));
        add_action('add_meta_boxes', array($this, 'add_popup_meta_boxes'));
        add_action('save_post', array($this, 'save_popup_meta_box'));
        add_action('wp_footer', array($this, 'display_active_popups'));
        add_action('wp_head', array($this, 'display_banner'));
    }

    public function create_popup_post_type() {
        register_post_type('popup', array(
            'labels'      => array(
                'name'          => __('Popups', 'custom-popups'),
                'singular_name' => __('Popup', 'custom-popups'),
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
        ));
    }

    public function add_popup_meta_boxes() {
        add_meta_box(
            'popup_status_meta_box',
            'Popup Details',
            array($this, 'render_popup_status_meta_box'),
            'popup',
            'side',
            'high'
        );
    }

    public function render_popup_status_meta_box($post) {
        $status = get_post_meta($post->ID, '_popup_status', true);
        $banner_content = get_post_meta($post->ID, '_banner_content', true);
        wp_nonce_field('save_popup_meta_box', 'popup_meta_box_nonce');
        ?>
        <label for="popup_status"><?php _e('Activate Popup:', 'custom-popups'); ?></label>
        <input type="checkbox" name="popup_status" id="popup_status" <?php checked($status, 'on'); ?> /><br><br>
        <label for="banner_content"><?php _e('Banner Content:', 'custom-popups'); ?></label>
        <textarea name="banner_content" id="banner_content" rows="4" style="width: 100%;"><?php echo esc_textarea($banner_content); ?></textarea>
        <?php
    }

    public function save_popup_meta_box($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['popup_meta_box_nonce']) || !wp_verify_nonce($_POST['popup_meta_box_nonce'], 'save_popup_meta_box')) {
            error_log('Nonce verification failed.');
            return $post_id;
        }
    
        // Check this is not an auto save routine.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            error_log('Doing autosave, skipping.');
            return $post_id;
        }
    
        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'popup' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                error_log('Current user cannot edit page.');
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                error_log('Current user cannot edit post.');
                return $post_id;
            }
        }
    
        // Update the popup status
        if (array_key_exists('popup_status', $_POST)) {
            update_post_meta($post_id, '_popup_status', 'on');
            error_log('Popup status set to on.');
        } else {
            update_post_meta($post_id, '_popup_status', 'off');
            error_log('Popup status set to off.');
        }
    
        // Update the banner content
        if (array_key_exists('banner_content', $_POST)) {
            update_post_meta($post_id, '_banner_content', sanitize_textarea_field($_POST['banner_content']));
            error_log('Banner content updated: ' . sanitize_textarea_field($_POST['banner_content']));
        }
    }
    

    public function display_active_popups() {
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
                $background_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                ?>
                <div class="popup" style="background-image: url('<?php echo esc_url($background_image_url); ?>');">
                    <span class="popup-close">&times;</span>
                    <h2 class="popup-title"><?php the_title(); ?></h2>
                    <div class="popup-content">
                        <?php the_content(); ?>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
        }

        wp_reset_postdata();
    }

    public function display_banner() {
        $args = array(
            'post_type' => 'popup',
            'meta_key' => '_popup_status',
            'meta_value' => 'on'
        );
        $query = new WP_Query($args);
    
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $banner_content = get_post_meta(get_the_ID(), '_banner_content', true);
                error_log('Retrieved banner content: ' . $banner_content);
                if ($banner_content) {
                    echo '<div id="custom-banner">' . esc_html($banner_content) . '</div>';
                }
            }
        }
    
        wp_reset_postdata();
    }
    
}
