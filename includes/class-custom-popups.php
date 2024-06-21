<?php

class Custom_Popups {

    public function run() {
        add_action('init', array($this, 'create_popup_post_type'));
        add_action('add_meta_boxes', array($this, 'add_popup_meta_boxes'));
        add_action('save_post', array($this, 'save_popup_meta_box'));
        add_action('wp_footer', array($this, 'display_active_popups'));
        add_action('wp_head', array($this, 'display_banner'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_color_picker'));
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
        $banner_bg_color = get_post_meta($post->ID, '_banner_bg_color', true);
        $banner_text_color = get_post_meta($post->ID, '_banner_text_color', true);
        $banner_size = get_post_meta($post->ID, '_banner_size', true);
        wp_nonce_field('save_popup_meta_box', 'popup_meta_box_nonce');
        ?>
        <label for="popup_status"><?php _e('Activate Popup:', 'custom-popups'); ?></label>
        <input type="checkbox" name="popup_status" id="popup_status" <?php checked($status, 'on'); ?> /><br><br>
        
        <label for="banner_content"><?php _e('Banner Content:', 'custom-popups'); ?></label>
        <textarea name="banner_content" id="banner_content" rows="4" style="width: 100%;"><?php echo esc_textarea($banner_content); ?></textarea><br><br>

        <label for="banner_bg_color"><?php _e('Banner Background Color: Eg- #EE3F61', 'custom-popups'); ?></label>
        <input type="text" name="banner_bg_color" id="banner_bg_color" value="<?php echo esc_attr($banner_bg_color); ?>" class="custom-color-picker" /><br><br>

        <label for="banner_text_color"><?php _e('Banner Text Color:Eg- #080B10', 'custom-popups'); ?></label>
        <input type="text" name="banner_text_color" id="banner_text_color" value="<?php echo esc_attr($banner_text_color); ?>" class="custom-color-picker" /><br><br>

        <label for="banner_size"><?php _e('Banner Size:', 'custom-popups'); ?></label>
        <select name="banner_size" id="banner_size">
            <option value="small" <?php selected($banner_size, 'small'); ?>><?php _e('Small', 'custom-popups'); ?></option>
            <option value="medium" <?php selected($banner_size, 'medium'); ?>><?php _e('Medium', 'custom-popups'); ?></option>
            <option value="large" <?php selected($banner_size, 'large'); ?>><?php _e('Large', 'custom-popups'); ?></option>
        </select>
        <?php
    }

    public function save_popup_meta_box($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['popup_meta_box_nonce']) || !wp_verify_nonce($_POST['popup_meta_box_nonce'], 'save_popup_meta_box')) {
            return $post_id;
        }
    
        // Check this is not an auto save routine.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
    
        // Check the user's permissions.
        if (isset($_POST['post_type']) && 'popup' == $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                return $post_id;
            }
        }
    
        // Update the popup status
        if (array_key_exists('popup_status', $_POST)) {
            update_post_meta($post_id, '_popup_status', 'on');
        } else {
            update_post_meta($post_id, '_popup_status', 'off');
        }
    
        // Update the banner content
        if (array_key_exists('banner_content', $_POST)) {
            update_post_meta($post_id, '_banner_content', sanitize_textarea_field($_POST['banner_content']));
        }

        // Update the banner background color
        if (array_key_exists('banner_bg_color', $_POST)) {
            update_post_meta($post_id, '_banner_bg_color', sanitize_hex_color($_POST['banner_bg_color']));
        }

        // Update the banner text color
        if (array_key_exists('banner_text_color', $_POST)) {
            update_post_meta($post_id, '_banner_text_color', sanitize_hex_color($_POST['banner_text_color']));
        }

        // Update the banner size
        if (array_key_exists('banner_size', $_POST)) {
            update_post_meta($post_id, '_banner_size', sanitize_text_field($_POST['banner_size']));
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
                $banner_bg_color = get_post_meta(get_the_ID(), '_banner_bg_color', true);
                $banner_text_color = get_post_meta(get_the_ID(), '_banner_text_color', true);
                $banner_size = get_post_meta(get_the_ID(), '_banner_size', true);
                
                if ($banner_content) {
                    echo '<div id="custom-banner" style="background-color: ' . esc_attr($banner_bg_color) . '; color: ' . esc_attr($banner_text_color) . '; font-size: ' . esc_attr($banner_size) . ';">' . esc_html($banner_content) . '</div>';
                }
            }
        }
    
        wp_reset_postdata();
    }

    public function enqueue_color_picker($hook_suffix) {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('custom-popups-color-picker', plugins_url('assets/js/custom-popups.js', __FILE__), array('wp-color-picker'), false, true);
    }
}
