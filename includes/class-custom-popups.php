<?php

class Custom_Popups {

    public function run() {
        add_action('init', array($this, 'create_popup_post_type'));
        add_action('add_meta_boxes', array($this, 'add_popup_meta_boxes'));
        add_action('save_post', array($this, 'save_popup_status_meta_box'));
        add_action('wp_footer', array($this, 'display_active_popups'));
        // add_action('wp_head', array($this, 'custom_popup_styles'));
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
            'Popup Status',
            array($this, 'render_popup_status_meta_box'),
            'popup',
            'side',
            'high'
        );
    }

    public function render_popup_status_meta_box($post) {
        $value = get_post_meta($post->ID, '_popup_status', true);
        ?>
        <label for="popup_status"><?php _e('Activate Popup:', 'custom-popups'); ?></label>
        <input type="checkbox" name="popup_status" id="popup_status" <?php checked($value, 'on'); ?> />
        <?php
    }

    public function save_popup_status_meta_box($post_id) {
        if (array_key_exists('popup_status', $_POST)) {
            update_post_meta($post_id, '_popup_status', 'on');
        } else {
            update_post_meta($post_id, '_popup_status', 'off');
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
                    <h2 class="pop-up-title"><?php the_title(); ?></h2>
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

    // public function custom_popup_styles() {
    //    
    // }
}
