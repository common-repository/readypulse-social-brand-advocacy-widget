<?php
/*
  Plugin Name: ReadyPulse Social Brand Advocacy Widget
  Description: Enables embedding of ReadyPulse testimonial showcase widgets on WordPress blogs using a short code. Ex [readypulse-widget id="widget-id" wptheme="false" type="gallery" width="600" height="500"]
  Version: 2.0
  Author: ReadyPulse
  Author URI: http://www.readypulse.com/
  License: GPLv2
 */
require_once ('RPWidgetSDK/iRPWidgetSettings.php');
require_once ('RPWidgetSDK/RPWidget.php');

class RPWordPressWidgetShortCodeData implements iRPWidgetSettings {

    var $settings;
    var $iRPWidgetSettings;

    function __construct($settings) {
        $this->settings = $settings;
    }

    /**
     * function getWidgetUrl
     * return string (the url of of widget)
     */
    function getWidgetUrl() {
        return $this->settings['widgeturl'];
    }

    /**
     * function useNativeLook
     * return boolean
     */
    function useNativeLook() {
        return ($this->settings['wptheme'] == "true" || strtolower($this->settings['wptheme']) == "yes" || $this->settings['wptheme'] == "on") ? true : false;
    }

    /**
     * function getWidgetType
     * returns string ('feed', 'album', 'gallery')
     */
    function getWidgetType() {
        return $this->settings['type'];
    }

    /**
     * function getWidgetWidth
     * returns string (width of widget)
     */
    function getWidgetWidth() {
        return $this->settings['width'];
    }

    /**
     * function getWidgetHeight
     * returns string (height of widget)
     */
    function getWidgetHeight() {
        return $this->settings['height'];
    }

    /**
     * function getWidgetScope
     * returns string (scope of widget)
     */
    function getWidgetScope() {
        return $this->settings['scope'];
    }

    /**
     * function showWidgetHeader
     * return boolean
     */
    function showWidgetHeader() {
        return ($this->settings['showheader'] == "true" || strtolower($this->settings['showheader']) == "yes" || $this->settings['showheader'] == "on") ? true : false;
    }

    /**
     * function showWidgetFooter
     * return boolean
     */
    function showWidgetFooter() {
        return ($this->settings['showfooter'] == "true" || strtolower($this->settings['showfooter']) == "yes" || $this->settings['showfooter'] == "on") ? true : false;
    }

    /**
     * function getWidgetId
     * return string
     */
    function getWidgetId() {
        return $this->settings['id'];
    }

    /**
     * function getThemeId
     * return string
     */
    function getThemeId() {
        return $this->settings['theme'];
    }

    /**
     * function getGetAgent
     * return string
     */
    function getAgent() {
        return 'wp-plugin';
    }

    /**
     * function getGetAgent
     * return string
     */
    function getRef() {
        return curPageURL();
    }

    public function getRPWidgetContent() {
        $this->iRPWidgetSettings['nativelook'] = $this->useNativeLook();
        $this->iRPWidgetSettings['widgeturl'] = $this->getWidgetUrl();
        $this->iRPWidgetSettings['height'] = $this->getWidgetHeight();
        $this->iRPWidgetSettings['scope'] = $this->getWidgetScope();
        $this->iRPWidgetSettings['type'] = $this->getWidgetType();
        $this->iRPWidgetSettings['width'] = $this->getWidgetWidth();
        $this->iRPWidgetSettings['id'] = $this->getWidgetId();
        $this->iRPWidgetSettings['theme'] = $this->getThemeId();
        $this->iRPWidgetSettings['showheader'] = $this->showWidgetHeader();
        $this->iRPWidgetSettings['showfooter'] = $this->showWidgetFooter();
        $this->iRPWidgetSettings['agent'] = $this->getAgent();
        $this->iRPWidgetSettings['ref'] = $this->getRef();
        $this->iRPWidgetSettings['src'] = 'wp';
        $this->iRPWidgetSettings['plugintype'] = 'wordpress';

        $rpwidget = New RPWidget($this->iRPWidgetSettings);
        $output = $rpwidget->getXTemplate();

        return $output;
    }

}

function curPageURL() {
    $pageURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    return $pageURL;
}

function ready_pulse_get_sort_code($atts) {

    extract(shortcode_atts(array(
                'url' => '',
                'wptheme' => "false",
                'type' => "feed",
                'id' => "1",
                'theme' => "",
                'width' => "",
                'height' => "",
                'showheader' => "true",
                'showfooter' => "true",
                'scope' => "",
                    ), $atts));

    $settings = array(
        'widgeturl' => $url,
        'wptheme' => $wptheme,
        'type' => $type,
        'width' => $width,
        'height' => $height,
        'scope' => $scope,
        'showheader' => $showheader,
        'showfooter' => $showfooter,
        'id' => $id,
        'theme' => $theme,
    );

    $rpwp = New RPWordPressWidgetShortCodeData($settings);

    $output = $rpwp->getRPWidgetContent();

    return $output;
}

function wp_smushit_filter_timeout_time($time) {
    $time = 50; //new number of seconds
    return $time;
}

add_filter('http_request_timeout', 'wp_smushit_filter_timeout_time');

add_shortcode('readypulse-widget', 'ready_pulse_get_sort_code');

add_filter('comment_text', 'do_shortcode');

function get_post_readypulse_data() {
    global $post; //wordpress post global object
    $data = array();
    if ($post->post_type == 'post') {
        $post_id = $post->ID;
        $data['readypulse_url'] = get_post_meta($post_id, 'readypulse_url', true);
        $data['readypulse_type'] = get_post_meta($post_id, 'readypulse_type', true);
        $data['readypulse_nativelook'] = get_post_meta($post_id, 'readypulse_nativelook', true);
        $data['readypulse_widget_id'] = get_post_meta($post_id, 'readypulse_widget_id', true);
        $data['readypulse_theme_id'] = get_post_meta($post_id, 'readypulse_theme_id', true);
        $data['readypulse_width'] = get_post_meta($post_id, 'readypulse_width', true);
        $data['readypulse_height'] = get_post_meta($post_id, 'readypulse_height', true);
        $data['readypulse_scope'] = get_post_meta($post_id, 'readypulse_scope', true);
        $data['readypulse_showheader'] = get_post_meta($post_id, 'readypulse_showheader', true);
        $data['readypulse_showfooter'] = get_post_meta($post_id, 'readypulse_showfooter', true);
    }
    return $data;
}

class ReadyPulseSidebarWidget extends WP_Widget {

    function ReadyPulseSidebarWidget() {
        // Instantiate the parent object
        parent::__construct(false, 'Readypulse Widget');
    }

    function widget($args, $instance) {
        // Widget output

        extract($args);
        global $wpdb;

        $title = $instance['title'];
        $readypulse_url = $instance['readypulse_url'];
        $readypulse_type = $instance['readypulse_type'];
        $readypulse_nativelook = $instance['readypulse_nativelook'];
        $readypulse_widget_id = $instance['readypulse_widget_id'];
        $readypulse_theme_id = $instance['readypulse_theme_id'];
        $readypulse_width = $instance['readypulse_width'];
        $readypulse_height = $instance['readypulse_height'];
        $readypulse_scope = $instance['readypulse_scope'];
        $readypulse_showheader = $instance['readypulse_showheader'];
        $readypulse_showfooter = $instance['readypulse_showfooter'];

        $post_data = get_post_readypulse_data();

        if (!empty($post_data)) {
            if ($post_data['readypulse_url'])
                $readypulse_url = $post_data['readypulse_url'];
            if ($post_data['readypulse_type'])
                $readypulse_type = $post_data['readypulse_type'];
            if ($post_data['readypulse_nativelook'])
                $readypulse_nativelook = $post_data['readypulse_nativelook'];
            if ($post_data['readypulse_widget_id'])
                $readypulse_widget_id = $post_data['readypulse_widget_id'];
            if ($post_data['readypulse_theme_id'])
                $readypulse_theme_id = $post_data['readypulse_theme_id'];
            if ($post_data['readypulse_width'])
                $readypulse_width = $post_data['readypulse_width'];
            if ($post_data['readypulse_height'])
                $readypulse_height = $post_data['readypulse_height'];
            if ($post_data['readypulse_scope'])
                $readypulse_scope = $post_data['readypulse_scope'];
            if ($post_data['readypulse_showheader'])
                $readypulse_showheader = $post_data['readypulse_showheader'];
            if ($post_data['readypulse_showfooter'])
                $readypulse_showfooter = $post_data['readypulse_showfooter'];
        }

        $settings = array(
            'widgeturl' => $readypulse_url,
            'wptheme' => $readypulse_nativelook,
            'type' => $readypulse_type,
            'width' => $readypulse_width,
            'height' => $readypulse_height,
            'scope' => $readypulse_scope,
            'showheader' => $readypulse_showheader,
            'showfooter' => $readypulse_showfooter,
            'id' => $readypulse_widget_id,
            'theme' => $readypulse_theme_id,
        );

        $rpwp = New RPWordPressWidgetShortCodeData($settings);

        $output = $rpwp->getRPWidgetContent();


        echo $before_widget;
        if ($title)
            echo $before_title . $title . $after_title;
        ?>
        <ul>
            <?php echo $output; ?>
        </ul>
        <?php
        echo $after_widget;
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['readypulse_url'] = strip_tags($new_instance['readypulse_url']);
        $instance['readypulse_nativelook'] = strip_tags($new_instance['readypulse_nativelook']);
        $instance['readypulse_type'] = strip_tags($new_instance['readypulse_type']);
        $instance['readypulse_widget_id'] = strip_tags($new_instance['readypulse_widget_id']);
        $instance['readypulse_theme_id'] = strip_tags($new_instance['readypulse_theme_id']);
        $instance['readypulse_width'] = strip_tags($new_instance['readypulse_width']);
        $instance['readypulse_height'] = strip_tags($new_instance['readypulse_height']);
        $instance['readypulse_scope'] = strip_tags($new_instance['readypulse_scope']);
        $instance['readypulse_showheader'] = strip_tags($new_instance['readypulse_showheader']);
        $instance['readypulse_showfooter'] = strip_tags($new_instance['readypulse_showfooter']);

        return $instance;
    }

    function form($instance) {
        $title = esc_attr($instance['title']);
        $readypulse_url = esc_attr($instance['readypulse_url']);
        $readypulse_type = esc_attr($instance['readypulse_type']);
        $readypulse_nativelook = esc_attr($instance['readypulse_nativelook']);
        $readypulse_widget_id = esc_attr($instance['readypulse_widget_id']);
        $readypulse_theme_id = esc_attr($instance['readypulse_theme_id']);
        $readypulse_width = esc_attr($instance['readypulse_width']);
        $readypulse_height = esc_attr($instance['readypulse_height']);
        $readypulse_scope = esc_attr($instance['readypulse_scope']);
        $readypulse_showheader = esc_attr($instance['readypulse_showheader']);
        $readypulse_showfooter = esc_attr($instance['readypulse_showfooter']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('readypulse_url'); ?>"><?php _e('Url'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('readypulse_url'); ?>" name="<?php echo $this->get_field_name('readypulse_url'); ?>" type="text" value="<?php echo $readypulse_url; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_nativelook'); ?>"><?php _e('Nativelook'); ?></label>
            <select name="<?php echo $this->get_field_name('readypulse_nativelook'); ?>" id="<?php echo $this->get_field_id('readypulse_nativelook'); ?>" class="widefat">
                <?php
                $options = array('Yes', 'No');
                foreach ($options as $option) {
                    echo '<option value="' . $option . '" id="' . $option . '"', $readypulse_nativelook == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_showheader'); ?>"><?php _e('Show Header'); ?></label>
            <select name="<?php echo $this->get_field_name('readypulse_showheader'); ?>" id="<?php echo $this->get_field_id('readypulse_showheader'); ?>" class="widefat">
                <?php
                $options = array('Yes', 'No');
                foreach ($options as $option) {
                    echo '<option value="' . $option . '" id="' . $option . '"', $readypulse_showheader == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_showfooter'); ?>"><?php _e('Show Header'); ?></label>
            <select name="<?php echo $this->get_field_name('readypulse_showfooter'); ?>" id="<?php echo $this->get_field_id('readypulse_showfooter'); ?>" class="widefat">
                <?php
                $options = array('Yes', 'No');
                foreach ($options as $option) {
                    echo '<option value="' . $option . '" id="' . $option . '"', $readypulse_showfooter == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_type'); ?>"><?php _e('Type'); ?></label>
            <select name="<?php echo $this->get_field_name('readypulse_type'); ?>" id="<?php echo $this->get_field_id('readypulse_type'); ?>" class="widefat">
                <?php
                $options = array('feed', 'album', 'gallery');
                foreach ($options as $option) {
                    echo '<option value="' . $option . '" id="' . $option . '"', $readypulse_type == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_widget_id'); ?>"><?php _e('Widget Id'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('readypulse_widget_id'); ?>" name="<?php echo $this->get_field_name('readypulse_widget_id'); ?>" type="text" value="<?php echo $readypulse_widget_id; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_theme_id'); ?>"><?php _e('Theme Id'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('readypulse_theme_id'); ?>" name="<?php echo $this->get_field_name('readypulse_theme_id'); ?>" type="text" value="<?php echo $readypulse_theme_id; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_width'); ?>"><?php _e('Width'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('readypulse_width'); ?>" name="<?php echo $this->get_field_name('readypulse_width'); ?>" type="text" value="<?php echo $readypulse_width; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('readypulse_height'); ?>"><?php _e('Height'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('readypulse_height'); ?>" name="<?php echo $this->get_field_name('readypulse_height'); ?>" type="text" value="<?php echo $readypulse_height; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('readypulse_scope'); ?>"><?php _e('Scope'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('readypulse_scope'); ?>" name="<?php echo $this->get_field_name('readypulse_scope'); ?>" type="text" value="<?php echo $readypulse_scope; ?>" />
        </p>
        <?php
    }

}

function myplugin_register_widgets() {
    register_widget('ReadyPulseSidebarWidget');
}

add_action('widgets_init', 'myplugin_register_widgets');
?>
<?php
/**
 * Register with hook 'wp_enqueue_scripts', which can be used for front end CSS and JavaScript
 */
add_action('wp_enqueue_scripts', 'readypulse_add_my_stylesheet');

/**
 * Enqueue plugin style-file
 */
function readypulse_add_my_stylesheet() {
    // Respects SSL, Style.css is relative to the current file
    wp_register_style('readypulse', plugins_url('rpwidget.css', __FILE__));
    wp_enqueue_style('readypulse');
    wp_enqueue_script('the_js', plugins_url('js/rpwidget.js', __FILE__), '', '', true);
}

$prefix = 'readypulse_';

$meta_box = array(
    'id' => 'readypulse-meta-box',
    'title' => 'Readypulse Siderbar Configuration',
    'page' => 'post',
    'context' => 'normal',
    'priority' => 'high',
    'fields' => array(
        array(
            'name' => 'Readypulse Url',
            'desc' => 'Enter Url of html data',
            'id' => $prefix . 'url',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'Use Nativelook',
            'id' => $prefix . 'nativelook',
            'type' => 'checkbox',
        ),
        array(
            'name' => 'Show Header',
            'id' => $prefix . 'showheader',
            'type' => 'checkbox',
        ),
        array(
            'name' => 'Show Footer',
            'id' => $prefix . 'showfooter',
            'type' => 'checkbox',
            'options' => array(
                array('name' => 'Yes', 'value' => 'yes'),
                array('name' => 'No', 'value' => 'no')
            ),
        ),
        array(
            'name' => 'Type',
            'id' => $prefix . 'type',
            'type' => 'radio',
            'options' => array(
                array('name' => 'Feed', 'value' => 'feed'),
                array('name' => 'Album', 'value' => 'Album'),
                array('name' => 'Gallery', 'value' => 'gallery')
            ),
        ),
        array(
            'name' => 'Readypulse Widget Id',
            'desc' => 'Please enter Widget Id',
            'id' => $prefix . 'widget_id',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'Readypulse Theme Id',
            'desc' => 'Please enter theme Id',
            'id' => $prefix . 'theme_id',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'ReadyPulse Width',
            'desc' => 'Please enter width',
            'id' => $prefix . 'width',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'ReadyPulse Height',
            'desc' => 'Please enter height',
            'id' => $prefix . 'height',
            'type' => 'text',
            'std' => ''
        ),
        array(
            'name' => 'ReadyPulse Scope',
            'desc' => 'Please enter scope',
            'id' => $prefix . 'scope',
            'type' => 'text',
            'std' => ''
        ),
    )
);

add_action('admin_menu', 'mytheme_add_box');

// Add meta box
function mytheme_add_box() {
    global $meta_box;

    add_meta_box($meta_box['id'], $meta_box['title'], 'mytheme_show_box', $meta_box['page'], $meta_box['context'], $meta_box['priority']);
}

// Callback function to show fields in meta box
function mytheme_show_box() {
    global $meta_box, $post;

    // Use nonce for verification
    echo '<input type="hidden" name="mytheme_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';

    echo '<table class="form-table">';

    foreach ($meta_box['fields'] as $field) {
        // get current post meta data
        $meta = get_post_meta($post->ID, $field['id'], true);

        echo '<tr>',
        '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>',
        '<td>';
        switch ($field['type']) {
            case 'text':
                echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:97%" />',
                '<br />', $field['desc'];
                break;
            case 'textarea':
                echo '<textarea name="', $field['id'], '" id="', $field['id'], '" cols="60" rows="4" style="width:97%">', $meta ? $meta : $field['std'], '</textarea>',
                '<br />', $field['desc'];
                break;
            case 'select':
                echo '<select name="', $field['id'], '" id="', $field['id'], '">';
                foreach ($field['options'] as $option) {
                    echo '<option', $meta == $option ? ' selected="selected"' : '', '>', $option, '</option>';
                }
                echo '</select>';
                break;
            case 'radio':
                foreach ($field['options'] as $option) {
                    echo '<input type="radio" name="', $field['id'], '" value="', $option['value'], '"', $meta == $option['value'] ? ' checked="checked"' : '', ' />', $option['name'];
                }
                break;
            case 'checkbox':
                echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />';
                break;
        }
        echo '<td>',
        '</tr>';
    }

    echo '</table>';
}

add_action('save_post', 'mytheme_save_data');

// Save data from meta box
function mytheme_save_data($post_id) {
    global $meta_box;

    // verify nonce
    if (!wp_verify_nonce($_POST['mytheme_meta_box_nonce'], basename(__FILE__))) {
        return $post_id;
    }

    // check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return $post_id;
        }
    } elseif (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    foreach ($meta_box['fields'] as $field) {
        $old = get_post_meta($post_id, $field['id'], true);
        $new = $_POST[$field['id']];

        if ($new && $new != $old) {
            update_post_meta($post_id, $field['id'], $new);
        } elseif ('' == $new && $old) {
            delete_post_meta($post_id, $field['id'], $old);
        }
    }
}
?>