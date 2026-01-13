<?php
/*
Plugin Name: Custom Site Navigator
Plugin URI: 
Description: A custom site navigation plugin that allows users to add and manage website shortcuts with logos
Version: 1.0
Author: Joynary
License: GPL v2 or later
Text Domain: custom-site-navigator
*/

if (!defined('ABSPATH')) {
    exit;
}

// Enqueue necessary scripts and styles
function csn_enqueue_scripts() {
    if (is_user_logged_in()) {
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('csn-script', plugins_url('js/custom-site-navigator.js', __FILE__), array('jquery', 'jquery-ui-sortable'), '1.0', true);
        wp_enqueue_style('csn-style', plugins_url('css/custom-site-navigator.css', __FILE__));
        
        wp_localize_script('csn-script', 'csnAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('csn-nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'csn_enqueue_scripts');

// Create database table on plugin activation
function csn_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_site_navigator';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        site_name varchar(255) NOT NULL,
        site_url text NOT NULL,
        site_logo text,
        display_order int(11) NOT NULL DEFAULT 0,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'csn_activate');

// Helper function to process URLs
function csn_process_url($url) {
    // Remove leading @ symbol if present
    $url = ltrim($url, '@');
    
    // Add https:// if no protocol is specified
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://" . $url;
    }
    
    // Encode special characters while preserving original structure
    $url = str_replace(' ', '%20', $url);
    
    // Handle special URLs (like Google sign-in URLs)
    if (strpos($url, 'accounts.google.com') !== false) {
        return $url; // Preserve Google authentication URLs as-is
    }
    
    return esc_url_raw($url);
}

// Add shortcode to display the navigator
function csn_display_navigator() {
    if (!is_user_logged_in()) {
        return '<div class="csn-container">
            <div style="text-align: center; padding: 40px 20px;">
                <p style="margin-bottom: 10px;">Please <a href="https://fusionveil.com/my-account" style="text-decoration: underline; color: #0073aa;">log in</a> to add your favorite websites and create your own personalized dashboard!</p>
            </div>
        </div>';
    }
    
    ob_start();
    include plugin_dir_path(__FILE__) . 'templates/navigator-template.php';
    return ob_get_clean();
}
add_shortcode('custom_site_navigator', 'csn_display_navigator');

// Include AJAX handlers
require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
