<?php
if (!defined('ABSPATH')) {
    exit;
}

// Add new site
function csn_add_site() {
    if (!check_ajax_referer('csn-nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $site_name = sanitize_text_field($_POST['site_name']);
    $site_url = csn_process_url($_POST['site_url']);
    
    // Get favicon
    $favicon_url = csn_get_favicon($site_url);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_site_navigator';
    
    $max_order = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MAX(display_order) FROM $table_name WHERE user_id = %d",
            get_current_user_id()
        )
    );
    
    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => get_current_user_id(),
            'site_name' => $site_name,
            'site_url' => $site_url,
            'site_logo' => $favicon_url,
            'display_order' => $max_order + 1
        ),
        array('%d', '%s', '%s', '%s', '%d')
    );
    
    if ($result) {
        wp_send_json_success(array(
            'id' => $wpdb->insert_id,
            'site_name' => $site_name,
            'site_url' => $site_url,
            'site_logo' => $favicon_url
        ));
    } else {
        wp_send_json_error('Failed to add site');
    }
}
add_action('wp_ajax_csn_add_site', 'csn_add_site');

// Delete site
function csn_delete_site() {
    if (!check_ajax_referer('csn-nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $site_id = intval($_POST['site_id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_site_navigator';
    
    $result = $wpdb->delete(
        $table_name,
        array(
            'id' => $site_id,
            'user_id' => get_current_user_id()
        ),
        array('%d', '%d')
    );
    
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to delete site');
    }
}
add_action('wp_ajax_csn_delete_site', 'csn_delete_site');

// Rename site
function csn_rename_site() {
    if (!check_ajax_referer('csn-nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $site_id = intval($_POST['site_id']);
    $new_name = sanitize_text_field($_POST['new_name']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_site_navigator';
    
    $result = $wpdb->update(
        $table_name,
        array('site_name' => $new_name),
        array(
            'id' => $site_id,
            'user_id' => get_current_user_id()
        ),
        array('%s'),
        array('%d', '%d')
    );
    
    if ($result !== false) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Failed to rename site');
    }
}
add_action('wp_ajax_csn_rename_site', 'csn_rename_site');

// Update order
function csn_update_order() {
    if (!check_ajax_referer('csn-nonce', 'nonce', false)) {
        wp_send_json_error('Invalid nonce');
    }
    
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    $order = array_map('intval', $_POST['order']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_site_navigator';
    
    foreach ($order as $position => $id) {
        $wpdb->update(
            $table_name,
            array('display_order' => $position),
            array(
                'id' => $id,
                'user_id' => get_current_user_id()
            ),
            array('%d'),
            array('%d', '%d')
        );
    }
    
    wp_send_json_success();
}
add_action('wp_ajax_csn_update_order', 'csn_update_order');

// Helper function to get favicon
function csn_get_favicon($url) {
    // Parse the URL and extract the domain
    $parsed_url = parse_url($url);
    
    // If URL parsing fails, return default icon
    if (!$parsed_url || !isset($parsed_url['host'])) {
        return 'data:text/plain;charset=utf-8,🌏';
    }
    
    // Get the base domain without subdomain
    $host_parts = explode('.', $parsed_url['host']);
    if (count($host_parts) > 2) {
        // Handle cases like "www.example.com" or "sub.example.com"
        $main_domain = $host_parts[count($host_parts)-2] . '.' . $host_parts[count($host_parts)-1];
    } else {
        $main_domain = $parsed_url['host'];
    }
    
    // Construct the root domain URL
    $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] : 'https';
    $domain = $scheme . '://' . $parsed_url['host'];
    
    // Try Google Favicon service with the main domain
    $google_favicon = 'https://www.google.com/s2/favicons?domain=' . urlencode($main_domain) . '&sz=64';
    
    // Try multiple favicon locations
    $favicon_locations = array(
        $domain . '/favicon.ico',
        $domain . '/favicon.png',
        $domain . '/apple-touch-icon.png',
        $domain . '/apple-touch-icon-precomposed.png',
        'https://icon.horse/icon/' . urlencode($main_domain),
        'https://www.google.com/s2/favicons?domain=' . urlencode($domain) . '&sz=64'
    );
    
    // First try Google Favicon service
    if (@getimagesize($google_favicon)) {
        return $google_favicon;
    }
    
    // Try each favicon location
    foreach ($favicon_locations as $favicon_url) {
        if (@getimagesize($favicon_url)) {
            return $favicon_url;
        }
    }
    
    // If no favicon found, return default icon
    return 'data:text/plain;charset=utf-8,🌏';
} 