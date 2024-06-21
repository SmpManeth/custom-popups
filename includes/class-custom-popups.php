<?php

class Custom_Popups
{

    // Register hooks
    public function run()
    {
        add_action('init', array($this, 'create_popup_post_type'));
        add_action('add_meta_boxes', array($this, 'add_popup_meta_boxes'));
        add_action('add_meta_boxes', array($this, 'add_banner_meta_box'));
        add_action('save_post', array($this, 'save_popup_meta_box'));
        add_action('wp_footer', array($this, 'display_active_popups'));
        add_action('wp_head', array($this, 'display_banner'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_color_picker'));
        add_filter('manage_popup_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_popup_posts_custom_column', array($this, 'render_custom_columns'), 10, 2);
        add_action('wp_ajax_toggle_popup_status', array($this, 'toggle_popup_status'));
    }

    // Create custom post type for popups
    public function create_popup_post_type()
    {
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

    // Add meta boxes for popup details
    public function add_popup_meta_boxes()
    {
        add_meta_box(
            'popup_status_meta_box',
            'Popup Details',
            array($this, 'render_popup_status_meta_box'),
            'popup',
            'side',
            'high'
        );
    }

    public function add_banner_meta_box()
    {
        add_meta_box(
            'banner_content_meta_box',
            'Banner Details',
            array($this, 'render_banner_content_meta_box'),
            'popup',
            'side',
            'high'
        );
    }


    // Render the meta box for popup details
    public function render_banner_content_meta_box($post)
    {
        $banner_content = get_post_meta($post->ID, '_banner_content', true);
        $banner_bg_color = get_post_meta($post->ID, '_banner_bg_color', true);
        $banner_text_color = get_post_meta($post->ID, '_banner_text_color', true);
        $banner_size = get_post_meta($post->ID, '_banner_size', true);
        wp_nonce_field('save_popup_meta_box', 'banner_meta_box_nonce');
?>
        <label for="banner_content"><?php _e('Banner Content:', 'custom-popups'); ?></label>
        <textarea name="banner_content" id="banner_content" rows="4" style="width: 100%;"><?php echo esc_textarea($banner_content); ?></textarea><br><br>

        <label for="banner_bg_color"><?php _e('Banner Background Color:', 'custom-popups'); ?></label>
        <input type="text" name="banner_bg_color" id="banner_bg_color" value="<?php echo esc_attr($banner_bg_color); ?>" class="custom-color-picker" /><br><br>

        <label for="banner_text_color"><?php _e('Banner Text Color:', 'custom-popups'); ?></label>
        <input type="text" name="banner_text_color" id="banner_text_color" value="<?php echo esc_attr($banner_text_color); ?>" class="custom-color-picker" /><br><br>

        <label for="banner_size"><?php _e('Banner Size:', 'custom-popups'); ?></label>
        <select name="banner_size" id="banner_size">
            <option value="small" <?php selected($banner_size, 'small'); ?>><?php _e('Small', 'custom-popups'); ?></option>
            <option value="medium" <?php selected($banner_size, 'medium'); ?>><?php _e('Medium', 'custom-popups'); ?></option>
            <option value="large" <?php selected($banner_size, 'large'); ?>><?php _e('Large', 'custom-popups'); ?></option>
        </select>
    <?php
    }


    // Render the meta box for popup details
    public function render_popup_status_meta_box($post)
    {
        $status = get_post_meta($post->ID, '_popup_status', true);
        $popup_bg_color = get_post_meta($post->ID, '_popup_bg_color', true);
        wp_nonce_field('save_popup_meta_box', 'popup_meta_box_nonce');
    ?>
        <label for="popup_status"><?php _e('Activate Popup:', 'custom-popups'); ?></label>
        <input type="checkbox" name="popup_status" id="popup_status" <?php checked($status, 'on'); ?> /><br><br>

        <label for="popup_bg_color"><?php _e('Popup Background Color:', 'custom-popups'); ?></label>
        <input type="text" name="popup_bg_color" id="popup_bg_color" value="<?php echo esc_attr($popup_bg_color); ?>" class="custom-color-picker" /><br><br>
        <?php
    }

    // Save the meta box data
    public function save_popup_meta_box($post_id)
    {
        // Check if our nonce is set and verify it
        if (!isset($_POST['popup_meta_box_nonce']) || !wp_verify_nonce($_POST['popup_meta_box_nonce'], 'save_popup_meta_box')) {
            return $post_id;
        }
        // Check if our nonce is set and verify it
        if (!isset($_POST['banner_meta_box_nonce']) || !wp_verify_nonce($_POST['banner_meta_box_nonce'], 'save_popup_meta_box')) {
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

        // Update the popup background color
        if (array_key_exists('popup_bg_color', $_POST)) {
            update_post_meta($post_id, '_popup_bg_color', sanitize_hex_color($_POST['popup_bg_color']));
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

    // Display active popups on the footer
    public function display_active_popups()
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
                $background_image_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
                $popup_bg_color = get_post_meta(get_the_ID(), '_popup_bg_color', true);
        ?>
                <div class="popup" style="background-image: url('<?php echo esc_url($background_image_url); ?>'); background: <?php echo $popup_bg_color ?>">
                    <span class="popup-close">&times;</span>
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

    // Display the banner on the head
    public function display_banner()
    {
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
                    echo '<div id="custom-banner" class="' . esc_attr($banner_size) . '" style="background-color: ' . esc_attr($banner_bg_color) . '; color: ' . esc_attr($banner_text_color) . ';">' . esc_html($banner_content) . '</div>';
                }
            }
        }

        wp_reset_postdata();
    }

    // Enqueue color picker scripts and styles
    public function enqueue_color_picker($hook_suffix)
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('custom-popups-color-picker', plugins_url('assets/js/custom-popups.js', __FILE__), array('wp-color-picker'), false, true);
    }

    // Add custom columns to the popup list table
    public function add_custom_columns($columns)
    {
        $columns['popup_status'] = __('Status', 'custom-popups');
        return $columns;
    }

    // Render the custom columns in the popup list table
    public function render_custom_columns($column, $post_id)
    {
        if ($column == 'popup_status') {
            $status = get_post_meta($post_id, '_popup_status', true);
            $status_label = $status == 'on' ? __('Active', 'custom-popups') : __('Inactive', 'custom-popups');
            $new_status = $status == 'on' ? 'off' : 'on';
            $nonce = wp_create_nonce('toggle_popup_status_' . $post_id);
            echo '<a href="#" class="toggle-popup-status" data-post-id="' . esc_attr($post_id) . '" data-new-status="' . esc_attr($new_status) . '" data-nonce="' . esc_attr($nonce) . '">' . esc_html($status_label) . '</a>';
        }
    }

    // Handle the AJAX request to toggle the popup status
    public function toggle_popup_status()
    {
        check_ajax_referer('toggle_popup_status_' . $_POST['post_id'], 'nonce');

        $post_id = intval($_POST['post_id']);
        $new_status = sanitize_text_field($_POST['new_status']);

        if (update_post_meta($post_id, '_popup_status', $new_status)) {
            wp_send_json_success(array('new_status' => $new_status));
        } else {
            wp_send_json_error();
        }
    }
}
